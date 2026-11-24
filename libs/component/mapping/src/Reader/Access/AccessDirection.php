<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Access;

use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Reader\PropertyAccessDirection;

/**
 * Direction of a member: the attribute and the config key declaring it, its
 * hook and its access in the draft.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
enum AccessDirection
{
    case Read;

    case Write;

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
     * @return 'get'|'set'
     */
    public function getConfigKey(): string
    {
        return match ($this) {
            self::Read => 'get',
            self::Write => 'set',
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

    public function findAccess(MemberDraft $member): ?AccessDraft
    {
        return match ($this) {
            self::Read => $member->read,
            self::Write => $member->write,
        };
    }
}
