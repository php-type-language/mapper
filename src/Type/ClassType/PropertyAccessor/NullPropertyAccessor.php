<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType\PropertyAccessor;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;

class NullPropertyAccessor implements PropertyAccessorInterface
{
    public function getValue(object $object, PropertyMetadata $meta): mixed
    {
        throw new \LogicException(\sprintf(
            'The %s::$%s property is not readable',
            $object::class,
            $meta->name,
        ));
    }

    public function isReadable(object $object, PropertyMetadata $meta): bool
    {
        return false;
    }

    public function setValue(object $object, PropertyMetadata $meta, mixed $value): void
    {
        throw new \LogicException(\sprintf(
            'The %s::$%s property is not writable',
            $object::class,
            $meta->name,
        ));
    }

    public function isWritable(object $object, PropertyMetadata $meta): bool
    {
        return false;
    }
}
