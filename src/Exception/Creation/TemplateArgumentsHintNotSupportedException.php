<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Creation;

use TypeLang\Mapper\Exception\StringInfo;

class TemplateArgumentsHintNotSupportedException extends TemplateArgumentHintException
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string $argument
     * @param non-empty-string $hint
     */
    public static function fromHintName(string $type, string $argument, string $hint, ?\Throwable $prev = null): self
    {
        $message = \vsprintf('Template hint %s of type %s defined on %s is not supported', [
            StringInfo::quoted($hint),
            $type,
            $argument,
        ]);

        return new static($message, previous: $prev);
    }
}
