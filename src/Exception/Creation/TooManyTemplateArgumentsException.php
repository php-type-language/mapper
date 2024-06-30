<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Creation;

use TypeLang\Mapper\Exception\StringInfo;

class TooManyTemplateArgumentsException extends TemplateArgumentsException
{
    /**
     * @param non-empty-string $type
     * @param int<0, max> $passed
     * @param int<0, max> $expectedMin
     * @param int<0, max> $expectedMax
     */
    public static function fromTemplateArgumentsCount(
        string $type,
        int $passed,
        int $expectedMin,
        int $expectedMax,
        ?\Throwable $prev = null,
    ): self {
        $message = \vsprintf('Type %s only accepts %s template arguments, but %d were passed', [
            StringInfo::quoted($type),
            StringInfo::formatRange($expectedMin, $expectedMax),
            $passed,
        ]);

        return new static($message, previous: $prev);
    }
}
