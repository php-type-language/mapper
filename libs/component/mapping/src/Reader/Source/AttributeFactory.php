<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Source;

use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;

/**
 * Creates the instances of the attributes of a place, an attribute that
 * cannot be created is an error.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class AttributeFactory
{
    /**
     * Returns the instances of the given class among the attributes that can
     * be created.
     *
     * @template TArg of object
     * @param list<\ReflectionAttribute<object>> $attributes
     * @param class-string<TArg> $class
     * @return list<TArg>
     */
    public static function create(
        array $attributes,
        string $class,
        SourceContext $context,
        ErrorCollector $errors,
    ): array {
        $result = [];

        foreach (self::createAll($attributes, $context, $errors) as $instance) {
            if ($instance instanceof $class) {
                $result[] = $instance;
            }
        }

        return $result;
    }

    /**
     * Returns the instances of the attributes that can be created.
     *
     * @param list<\ReflectionAttribute<object>> $attributes
     * @return list<object>
     */
    public static function createAll(array $attributes, SourceContext $context, ErrorCollector $errors): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            try {
                $result[] = $attribute->newInstance();
            } catch (\Throwable $e) {
                $errors->addError(InvalidAttributeException::becauseAttributeCannotBeCreated(
                    attribute: $attribute->getName(),
                    target: $context->target,
                    source: $context->source,
                    previous: $e,
                ));
            }
        }

        return $result;
    }
}
