<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\MapType;
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
 * Types of a property and their strictness. {@see MapType} of the property
 * sets both types, of its `get` hook the read type, of its `set` hook the
 * write type, of any other method is an error. The `type`, `read_type` and
 * `write_type` keys set the types, the `strict` key the strictness of the
 * types of the config or, in case of it sets none, of the lower readers.
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
        $type = $this->findType($property->getAttributes(MapType::class), $context, $errors);

        if ($type !== null) {
            $metadata = $metadata->withType($type);
        }

        $read = $this->findType(self::getHookAttributes(PropertyHooks::findGet($property)), $context, $errors);

        if ($read !== null) {
            $metadata = $metadata->withReadType($read);
        }

        $write = $this->findType(self::getHookAttributes(PropertyHooks::findSet($property)), $context, $errors);

        if ($write !== null) {
            $metadata = $metadata->withWriteType($write);
        }

        return $metadata;
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
        $isRead = false;
        $isWrite = false;

        foreach (TypeDirection::cases() as $direction) {
            if (!$config->has($direction->value)) {
                continue;
            }

            $type = $this->parseConfigType($config, $direction->value, $errors);

            if ($type !== null && $direction->isRead()) {
                $metadata = $metadata->withReadType($type);
                $isRead = true;
            }

            if ($type !== null && $direction->isWrite()) {
                $metadata = $metadata->withWriteType($type);
                $isWrite = true;
            }
        }

        if (!$config->has(self::STRICT_KEY)) {
            return $metadata;
        }

        // strict: true
        if (!$isRead && !$isWrite) {
            $isRead = true;
            $isWrite = true;
        }

        return self::readStrict($config, $metadata, $isRead, $isWrite, $errors);
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
     * Returns the type of the last {@see MapType} of the list, {@see null} in
     * case of there is none or it cannot be created.
     *
     * @param list<\ReflectionAttribute<object>> $attributes
     */
    private function findType(array $attributes, SourceContext $context, ErrorCollector $errors): ?TypeMetadata
    {
        $type = null;

        foreach (AttributeFactory::create($attributes, MapType::class, $context, $errors) as $attribute) {
            $type = $context->parseType($this->parser, $attribute->type);

            if ($attribute->strict !== null) {
                $type = $type->withStrict($attribute->strict);
            }
        }

        return $type;
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

    private static function readStrict(
        ConfigSection $config,
        PropertyMetadata $metadata,
        bool $isRead,
        bool $isWrite,
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

        if ($isRead && $metadata->read !== null) {
            $metadata = $metadata->withReadType($metadata->read->withStrict($isStrict));
        }

        if ($isWrite && $metadata->write !== null) {
            $metadata = $metadata->withWriteType($metadata->write->withStrict($isStrict));
        }

        return $metadata;
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
