<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;

interface PropertyAccessorInterface
{
    /**
     * @throws \Throwable occurs in case of value cannot be reads
     */
    public function getValue(object $object, PropertyMetadata $meta): mixed;

    public function isReadable(object $object, PropertyMetadata $meta): bool;

    /**
     * @throws \Throwable occurs in case of value cannot be writes
     */
    public function setValue(object $object, PropertyMetadata $meta, mixed $value): void;

    public function isWritable(object $object, PropertyMetadata $meta): bool;
}
