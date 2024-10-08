<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

/**
 * @deprecated TODO
 */
class UnsupportedMetadataException extends \LogicException implements MapperExceptionInterface
{
    public static function fromMetadataName(object $metadata, ?\Throwable $prev = null): self
    {
        $message = \sprintf('Unsupported metadata %s', $metadata::class);

        return new self($message, previous: $prev);
    }
}
