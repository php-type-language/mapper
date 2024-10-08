<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Creation;

use TypeLang\Mapper\Exception\StringInfo;

/**
 * @deprecated TODO
 */
class TemplateArgumentsNotSupportedException extends TemplateArgumentsException
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string|null $given
     */
    public static function fromTypeName(string $type, ?string $given = null, ?\Throwable $prev = null): self
    {
        $message = \sprintf('Type %s does not support template arguments', StringInfo::quoted($type));

        if ($given !== null) {
            $message .= \sprintf(', but %s given', $given);
        }

        return new static($message, previous: $prev);
    }
}
