<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;

abstract class PropertyAccessor implements PropertyAccessorInterface
{
    public function __construct(
        protected PropertyAccessorInterface $delegate = new NullPropertyAccessor(),
    ) {}

    public function getValue(object $object, PropertyMetadata $meta): mixed
    {
        return $this->delegate->getValue($object, $meta);
    }

    public function isReadable(object $object, PropertyMetadata $meta): bool
    {
        return $this->delegate->isReadable($object, $meta);
    }

    public function setValue(object $object, PropertyMetadata $meta, mixed $value): void
    {
        $this->delegate->setValue($object, $meta, $value);
    }

    public function isWritable(object $object, PropertyMetadata $meta): bool
    {
        return $this->delegate->isWritable($object, $meta);
    }
}
