<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

/**
 * The {@see \iterator_to_array()} function supports {@see array} only starting
 * with PHP 8.2. To ensure compatibility, you can use this function.
 *
 * @template TArgValue of mixed
 *
 * @return list<TArgValue>
 */
function iterable_to_array(iterable $iterator, bool $preserveKeys = true): array
{
    if (\PHP_VERSION_ID >= 80200 || !\is_array($iterator)) {
        return \iterator_to_array($iterator, $preserveKeys);
    }

    if ($preserveKeys === true || \array_is_list($iterator)) {
        return $iterator;
    }

    return \array_values($iterator);
}
