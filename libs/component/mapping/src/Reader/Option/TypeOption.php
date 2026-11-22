<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\MethodAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Option\TypeOption\TypeDirection;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Types of the directions of a property and its strictness on
 * denormalization. No source adds a direction.
 *
 * {@see MapType} of the property sets the types of its directions, of its
 * `get` hook or getter the read type, of its `set` hook or setter the write
 * type. The attribute of a hook the mapper does not access the property
 * through, of any other method and `strict` of a getter are errors.
 * `strict` of the property, the `set` hook and the setter attributes is the
 * strictness of the property.
 *
 * The `type`, `read_type` and `write_type` keys set the types, `read_type`
 * and `write_type` of a missing direction are errors. The `strict` key sets
 * the strictness.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class TypeOption implements PropertyOptionInterface, ClassOptionInterface
{
    private const STRICT_KEY = 'strict';

    public function __construct(
        /**
         * @var TypeParserInterface<TypeNode, string>
         */
        private readonly TypeParserInterface $parser,
    ) {}

    public function hasPropertyAttributes(\ReflectionProperty $property): bool
    {
        return $property->getAttributes(MapType::class) !== []
            || self::getHookAttributes(PropertyHooks::findGet($property)) !== []
            || self::getHookAttributes(PropertyHooks::findSet($property)) !== [];
    }

    public function readPropertyAttributes(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $type = self::findAttribute($property->getAttributes(MapType::class), $context, $errors);

        if ($type !== null) {
            $metadata = $metadata->withType($this->parseAttributeType($type, $context));
            $metadata = self::readStrictAttribute($metadata, $type);
        }

        $get = PropertyHooks::findGet($property);
        $read = self::findAttribute(self::getHookAttributes($get), $context, $errors);

        if ($get !== null && $read !== null) {
            $metadata = $this->readGetHookAttribute($metadata, $get, $read, $context, $errors);
        }

        $set = PropertyHooks::findSet($property);
        $write = self::findAttribute(self::getHookAttributes($set), $context, $errors);

        if ($set !== null && $write !== null) {
            $metadata = $this->readSetHookAttribute($metadata, $set, $write, $context, $errors);
        }

        return $metadata;
    }

    /**
     * Sets the read type of the property read through the hook, the
     * attribute of a bypassed hook and its strictness are errors.
     */
    private function readGetHookAttribute(
        PropertyMetadata $metadata,
        \ReflectionMethod $hook,
        MapType $attribute,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $source = SourceInfo::createFromReflection($hook);

        if (!$metadata->read?->point instanceof HookAccessPoint) {
            self::addBypassedHookError($hook, 'get', $context, $errors);

            return $metadata;
        }

        if ($attribute->strict !== null) {
            $errors->addError(InvalidAttributeException::becauseStrictIsOnGetter(
                attribute: MapType::class,
                property: $context->target,
                source: $source,
            ));
        }

        return $metadata->withReadType($this->parseAttributeType($attribute, $context));
    }

    /**
     * Sets the write type and the strictness of the property written
     * through the hook, the strictness of the hook overrides the one of the
     * property. The attribute of a bypassed hook is an error.
     */
    private function readSetHookAttribute(
        PropertyMetadata $metadata,
        \ReflectionMethod $hook,
        MapType $attribute,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        if (!$metadata->write?->point instanceof HookAccessPoint) {
            self::addBypassedHookError($hook, 'set', $context, $errors);

            return $metadata;
        }

        $metadata = $metadata->withWriteType($this->parseAttributeType($attribute, $context));

        return self::readStrictAttribute($metadata, $attribute);
    }

    /**
     * Adds the error of {@see MapType} of the hook the mapper does not
     * access the property through, a hook chosen as the point of a higher
     * reader is skipped.
     *
     * @param 'get'|'set' $name
     */
    private static function addBypassedHookError(
        \ReflectionMethod $hook,
        string $name,
        SourceContext $context,
        ErrorCollector $errors,
    ): void {
        if (self::isChosenPoint($hook)) {
            return;
        }

        $errors->addError(InvalidAttributeException::becauseHookIsBypassed(
            attribute: MapType::class,
            hook: $name,
            property: $context->target,
            source: SourceInfo::createFromReflection($hook),
        ));
    }

    public function getPropertyKeys(): array
    {
        return [
            TypeDirection::Both->value,
            TypeDirection::Read->value,
            TypeDirection::Write->value,
            self::STRICT_KEY,
        ];
    }

    public function readPropertyConfig(
        ConfigSection $config,
        PropertyMetadata $metadata,
        ErrorCollector $errors,
    ): PropertyMetadata {
        foreach (TypeDirection::cases() as $direction) {
            if (!$config->has($direction->value)) {
                continue;
            }

            $type = $this->parseConfigType($config, $direction->value, $errors);

            if ($type !== null) {
                $metadata = self::readConfigType($config, $direction, $metadata, $type, $errors);
            }
        }

        if (!$config->has(self::STRICT_KEY)) {
            return $metadata;
        }

        return self::readStrictConfig($config, $metadata, $errors);
    }

    /**
     * Sets the types of the directions accessed through a method by
     * {@see MapType} of the method, {@see MapType} of any other method that
     * is not a hook is an error.
     */
    public function readClassAttributes(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassMetadata {
        foreach ($class->getMethods() as $method) {
            $attributes = $method->getAttributes(MapType::class);

            if ($attributes === []) {
                continue;
            }

            $isPoint = false;

            foreach ($metadata->properties as $property) {
                $isRead = self::isMethodPoint($property->read, $method);
                $isWrite = self::isMethodPoint($property->write, $method);

                if (!$isRead && !$isWrite) {
                    continue;
                }

                $isPoint = true;
                $context = $contexts->createFromMethod($class, $method, $property->name);
                $attribute = self::findAttribute($attributes, $context, $errors);

                if ($attribute === null) {
                    continue;
                }

                if ($isRead) {
                    $property = $this->readGetterAttribute($property, $method, $attribute, $context, $errors);
                }

                if ($isWrite) {
                    $property = $property->withWriteType($this->parseAttributeType($attribute, $context));
                    $property = self::readStrictAttribute($property, $attribute);
                }

                $metadata = $metadata->withProperty($property);
            }

            // a higher reader has chosen another point of the method
            if ($isPoint || self::isChosenPoint($method)) {
                continue;
            }

            foreach ($attributes as $attribute) {
                $errors->addError(InvalidAttributeException::becauseAttributeIsOnMethod(
                    attribute: $attribute->getName(),
                    method: $class->name . '::' . $method->name . '()',
                    source: SourceInfo::createFromReflection($method),
                ));
            }
        }

        return $metadata;
    }

    /**
     * Returns {@see true} in case of the hook or the method is chosen as an
     * access point by its attribute: {@see MapType} of it is not applied
     * when a higher reader chooses another point.
     */
    private static function isChosenPoint(\ReflectionMethod $method): bool
    {
        return $method->getAttributes(MapGetter::class) !== []
            || $method->getAttributes(MapSetter::class) !== [];
    }

    private static function isMethodPoint(?AccessMetadata $access, \ReflectionMethod $method): bool
    {
        $point = $access?->point;

        return $point instanceof MethodAccessPoint
            && \strcasecmp($point->method, $method->name) === 0;
    }

    /**
     * Sets the read type of the property read through the method, the
     * strictness of a getter is an error.
     */
    private function readGetterAttribute(
        PropertyMetadata $metadata,
        \ReflectionMethod $method,
        MapType $attribute,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        if ($attribute->strict !== null) {
            $errors->addError(InvalidAttributeException::becauseStrictIsOnGetter(
                attribute: MapType::class,
                property: $context->target,
                source: SourceInfo::createFromReflection($method),
            ));
        }

        return $metadata->withReadType($this->parseAttributeType($attribute, $context));
    }

    public function getClassKeys(): array
    {
        return [];
    }

    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassMetadata $metadata,
        ErrorCollector $errors,
    ): ClassMetadata {
        return $metadata;
    }

    /**
     * Returns the last {@see MapType} of the list, {@see null} in case of
     * there is none or it cannot be created.
     *
     * @param list<\ReflectionAttribute<object>> $attributes
     */
    private static function findAttribute(array $attributes, SourceContext $context, ErrorCollector $errors): ?MapType
    {
        $result = null;

        foreach (AttributeFactory::create($attributes, MapType::class, $context, $errors) as $attribute) {
            $result = $attribute;
        }

        return $result;
    }

    private function parseAttributeType(MapType $attribute, SourceContext $context): TypeMetadata
    {
        return $context->parseType($this->parser, $attribute->type);
    }

    /**
     * The attribute without the strictness keeps the given one.
     */
    private static function readStrictAttribute(PropertyMetadata $metadata, MapType $attribute): PropertyMetadata
    {
        if ($attribute->strict === null) {
            return $metadata;
        }

        return $metadata->withStrict($attribute->strict);
    }

    /**
     * Sets the type of the direction of the key, the key of a missing
     * direction is an error.
     */
    private static function readConfigType(
        ConfigSection $config,
        TypeDirection $direction,
        PropertyMetadata $metadata,
        TypeMetadata $type,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $context = $config->createContext($direction->value);

        return match ($direction) {
            TypeDirection::Both => $metadata->withType($type),
            TypeDirection::Read => self::readConfigReadType($context, $metadata, $type, $errors),
            TypeDirection::Write => self::readConfigWriteType($context, $metadata, $type, $errors),
        };
    }

    private static function readConfigReadType(
        SourceContext $context,
        PropertyMetadata $metadata,
        TypeMetadata $type,
        ErrorCollector $errors,
    ): PropertyMetadata {
        if ($metadata->read !== null) {
            return $metadata->withReadType($type);
        }

        $errors->addError(InvalidOptionValueException::becauseDirectionIsMissing(
            TypeDirection::Read->value,
            'normalized',
            $context->target,
            $context->source,
        ));

        return $metadata;
    }

    private static function readConfigWriteType(
        SourceContext $context,
        PropertyMetadata $metadata,
        TypeMetadata $type,
        ErrorCollector $errors,
    ): PropertyMetadata {
        if ($metadata->write !== null) {
            return $metadata->withWriteType($type);
        }

        $errors->addError(InvalidOptionValueException::becauseDirectionIsMissing(
            TypeDirection::Write->value,
            'denormalized',
            $context->target,
            $context->source,
        ));

        return $metadata;
    }

    /**
     * @param non-empty-string $key
     */
    private function parseConfigType(ConfigSection $config, string $key, ErrorCollector $errors): ?TypeMetadata
    {
        $value = $config->get($key);
        $context = $config->createContext($key);

        if (\is_string($value) && $value !== '') {
            return $context->parseType($this->parser, $value);
        }

        $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
            $key,
            $context->target,
            $context->source,
        ));

        return null;
    }

    private static function readStrictConfig(
        ConfigSection $config,
        PropertyMetadata $metadata,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $isStrict = $config->get(self::STRICT_KEY);

        if (!\is_bool($isStrict)) {
            $context = $config->createContext(self::STRICT_KEY);
            $errors->addError(InvalidOptionValueException::becauseValueIsNotBoolean(
                self::STRICT_KEY,
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        return $metadata->withStrict($isStrict);
    }

    /**
     * Returns {@see MapType} attributes of the hook, an empty list in case
     * of there is no hook.
     *
     * @return list<\ReflectionAttribute<object>>
     */
    private static function getHookAttributes(?\ReflectionMethod $hook): array
    {
        return $hook?->getAttributes(MapType::class) ?? [];
    }
}
