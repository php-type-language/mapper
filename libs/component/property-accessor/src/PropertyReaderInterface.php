<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor;

/**
 * Reads one property of any object of its class. The way of reading is
 * chosen once by {@see PropertyAccessorFactory::createReader()},
 * the reading itself does no lookups.
 *
 * The object must be of the class of the property, the reader does not
 * check it.
 *
 * ```
 * $class = new \ReflectionClass(User::class);
 * $reader = $factory->createReader($class, $metadata);
 *
 * echo $reader->read($user); // user@example.com
 * ```
 *
 * @template-contravariant TObject of object
 * @template-covariant TValue
 */
interface PropertyReaderInterface
{
    /**
     * Exceptions to a get hook are thrown as is.
     *
     * @param TObject $object
     * @return TValue
     * @throws \Error in case of the property is not initialized
     */
    public function read(object $object): mixed;
}
