<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

/**
 * Cache rejects the key of metadata, the prefix of the keys is not valid
 * for it.
 */
final class InvalidCacheKeyException extends ReaderException
{
    public static function becauseKeyIsRejected(string $key, \Throwable $previous): self
    {
        $message = \sprintf(
            'Cache rejects the key "%s" of metadata, check the prefix of the keys: %s',
            \addcslashes($key, '"'),
            $previous->getMessage(),
        );

        return new self($message, 0, $previous);
    }
}
