<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Other values of the data denormalized to the case of a backed enum, such
 * as the former values of the case. The case is normalized to its own
 * value.
 *
 * ```
 * enum Status: string
 * {
 *     #[MapAlias('active', 'on')]
 *     case Enabled = 'enabled';
 * }
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT | \Attribute::IS_REPEATABLE)]
final class MapAlias
{
    /**
     * @var list<int|string>
     */
    public readonly array $values;

    public function __construct(int|string ...$values)
    {
        $this->values = \array_values($values);
    }
}
