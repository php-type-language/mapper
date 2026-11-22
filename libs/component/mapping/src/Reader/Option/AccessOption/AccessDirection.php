<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option\AccessOption;

use TypeLang\Mapper\Mapping\Direction;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\Metadata\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Reader\PropertyAccessDirection;

/**
 * Direction whose access point is chosen: what it is chosen by and where it
 * is kept in the metadata.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
enum AccessDirection
{
    /**
     * Normalization, the getter
     */
    case Read;

    /**
     * Denormalization, the setter
     */
    case Write;

    public static function fromDirection(Direction $direction): self
    {
        return match ($direction) {
            Direction::Normalization => self::Read,
            Direction::Denormalization => self::Write,
        };
    }

    public function getOther(): self
    {
        return match ($this) {
            self::Read => self::Write,
            self::Write => self::Read,
        };
    }

    /**
     * @return class-string<MapGetter|MapSetter>
     */
    public function getAttribute(): string
    {
        return match ($this) {
            self::Read => MapGetter::class,
            self::Write => MapSetter::class,
        };
    }

    /**
     * @return non-empty-string
     */
    public function getConfigKey(): string
    {
        return match ($this) {
            self::Read => 'getter',
            self::Write => 'setter',
        };
    }

    /**
     * @return 'get'|'set'
     */
    public function getHookName(): string
    {
        return match ($this) {
            self::Read => 'get',
            self::Write => 'set',
        };
    }

    /**
     * Returns the word of the messages: the property is not normalized.
     *
     * @return 'normalized'|'denormalized'
     */
    public function getParticiple(): string
    {
        return match ($this) {
            self::Read => 'normalized',
            self::Write => 'denormalized',
        };
    }

    public function getNativeDirection(): PropertyAccessDirection
    {
        return match ($this) {
            self::Read => PropertyAccessDirection::Read,
            self::Write => PropertyAccessDirection::Write,
        };
    }

    public function findHook(\ReflectionProperty $property): ?\ReflectionMethod
    {
        return match ($this) {
            self::Read => PropertyHooks::findGet($property),
            self::Write => PropertyHooks::findSet($property),
        };
    }

    public function findAccess(PropertyMetadata $metadata): ?AccessMetadata
    {
        return match ($this) {
            self::Read => $metadata->read,
            self::Write => $metadata->write,
        };
    }

    public function withAccess(PropertyMetadata $metadata, ?AccessMetadata $access): PropertyMetadata
    {
        return match ($this) {
            self::Read => $metadata->withReadAccess($access),
            self::Write => $metadata->withWriteAccess($access),
        };
    }
}
