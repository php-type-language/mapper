<?php

declare(strict_types=1);

namespace Serafim\Mapper\Exception\Creation;

class ShapeFieldsNotSupportedException extends ShapeFieldsException
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string|null $given
     */
    public static function fromTypeName(string $type, ?string $given = null, ?\Throwable $prev = null): self
    {
        $message = \sprintf('Type "%s" does not support shape fields', $type);

        if ($given !== null) {
            $message .= \sprintf(', but %s given', $given);
        }

        return new static($message, previous: $prev);
    }
}
