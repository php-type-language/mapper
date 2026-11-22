<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor;

/**
 * Writes one property of any object of its class. The way of writing is
 * chosen once by {@see PropertyAccessorFactory::createWriter()},
 * the writing itself does no lookups.
 *
 * The object must be of the class of the property, the writer does not
 * check it. The value is not checked or converted by the writer: a public
 * property rejects a value of another type, any other property is assigned
 * by the rules of the coercive typing mode.
 *
 * ```
 * $class = new \ReflectionClass(User::class);
 * $writer = $factory->createWriter($class, $metadata);
 *
 * $writer->write($user, 'user@example.com');
 * ```
 *
 * @template-contravariant TObject of object
 * @template-contravariant TValue
 */
interface PropertyWriterInterface
{
    /**
     * Exceptions of a set hook are thrown as is. Whether a scalar of
     * another type is coerced depends on the writer.
     *
     * @param TObject $object
     * @param TValue $value
     * @throws \TypeError in case of the writer does not take the value for
     *         the type of the property
     * @throws \Error in case of the `readonly` property is already
     *         initialized
     */
    public function write(object $object, mixed $value): void;
}
