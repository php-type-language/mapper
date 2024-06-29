<?php

declare(strict_types=1);

namespace Serafim\Mapper\Exception\Creation;

class TemplateArgumentsHintNotSupportedException extends TemplateArgumentHintException
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string $argument
     * @param non-empty-string $hint
     */
    public static function fromHintName(string $type, string $argument, string $hint, ?\Throwable $prev = null): self
    {
        $message = \vsprintf('Template hint "%s" of type %s defined on %s is not supported', [
            $hint,
            $type,
            $argument,
        ]);

        return new static($message, previous: $prev);
    }
}
