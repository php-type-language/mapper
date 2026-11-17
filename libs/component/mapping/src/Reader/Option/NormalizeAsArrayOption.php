<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;

/**
 * Form of the normalized objects of a class: {@see NormalizeAsArray} and
 * the `normalize_as_array` key.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class NormalizeAsArrayOption implements ClassOptionInterface
{
    private const KEY = 'normalize_as_array';

    public function readClassAttributes(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassMetadata {
        $attributes = $class->getAttributes(NormalizeAsArray::class);

        if ($attributes === []) {
            return $metadata;
        }

        $context = $contexts->createFromClass($class);

        foreach (AttributeFactory::create($attributes, NormalizeAsArray::class, $context, $errors) as $attribute) {
            $metadata = $metadata->withNormalizeAsArray($attribute->enabled);
        }

        return $metadata;
    }

    public function getClassKeys(): array
    {
        return [self::KEY];
    }

    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassMetadata $metadata,
        ErrorCollector $errors,
    ): ClassMetadata {
        if (!$config->has(self::KEY)) {
            return $metadata;
        }

        $value = $config->get(self::KEY);

        if (\is_bool($value)) {
            return $metadata->withNormalizeAsArray($value);
        }

        $context = $config->createContext(self::KEY);
        $errors->addError(InvalidOptionValueException::becauseValueIsNotBoolean(
            self::KEY,
            $context->target,
            $context->source,
        ));

        return $metadata;
    }
}
