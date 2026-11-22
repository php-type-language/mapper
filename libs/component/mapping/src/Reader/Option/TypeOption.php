<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
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
 * `get` hook the read type, of its `set` hook the write type. The attribute
 * of a hook the mapper does not access the property through, of any other
 * method and `strict` of the `get` hook are errors. `strict` of the property
 * and the `set` hook attributes is the strictness of the property.
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
            $errors->addError(InvalidAttributeException::becauseHookIsBypassed(
                attribute: MapType::class,
                hook: 'get',
                property: $context->target,
                source: $source,
            ));

            return $metadata;
        }

        if ($attribute->strict !== null) {
            $errors->addError(InvalidAttributeException::becauseStrictIsOnGetHook(
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
            $errors->addError(InvalidAttributeException::becauseHookIsBypassed(
                attribute: MapType::class,
                hook: 'set',
                property: $context->target,
                source: SourceInfo::createFromReflection($hook),
            ));

            return $metadata;
        }

        $metadata = $metadata->withWriteType($this->parseAttributeType($attribute, $context));

        return self::readStrictAttribute($metadata, $attribute);
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
     * Adds an error for each {@see MapType} of a method that is not a
     * property hook.
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

            $source = SourceInfo::createFromReflection($method);

            foreach ($attributes as $attribute) {
                $errors->addError(InvalidAttributeException::becauseAttributeIsOnMethod(
                    attribute: $attribute->getName(),
                    method: $class->name . '::' . $method->name . '()',
                    source: $source,
                ));
            }
        }

        return $metadata;
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

        $errors->addError(InvalidOptionValueException::becausePropertyIsNotNormalized(
            TypeDirection::Read->value,
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

        $errors->addError(InvalidOptionValueException::becausePropertyIsNotDenormalized(
            TypeDirection::Write->value,
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
