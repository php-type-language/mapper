<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Creation;

class UnsupportedMetadataException extends TypeCreationException
{
    public static function fromMetadataName(object $metadata, \Throwable $prev = null): static
    {
        $message = \sprintf('Unsupported metadata %s', $metadata::class);

        return new static($message, previous: $prev);
    }
}
