<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Config has a key the reader does not know.
 */
final class UnknownKeyException extends MetadataException
{
    /**
     * @param string $target property or class: `App\User::$id`, `App\User`
     */
    public static function becauseKeyIsUnknown(int|string $key, string $target, SourceInfo $source): self
    {
        $message = \sprintf('unknown key "%s"', \addcslashes((string) $key, '"'));

        return new self($target, $source, $message);
    }
}
