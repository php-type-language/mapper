<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option\TypeOption;

/**
 * Direction of a mapping the type of a config key is set for. The value is
 * the key itself.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
enum TypeDirection: string
{
    /**
     * Key `type`: both directions
     */
    case Both = 'type';

    /**
     * Key `read_type`
     */
    case Read = 'read_type';

    /**
     * Key `write_type`
     */
    case Write = 'write_type';

    public function isRead(): bool
    {
        return $this !== self::Write;
    }

    public function isWrite(): bool
    {
        return $this !== self::Read;
    }
}
