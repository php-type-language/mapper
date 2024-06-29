<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Creation;

use TypeLang\Mapper\Exception\StringInfo;

class MissingTemplateArgumentsException extends TemplateArgumentsException
{
    /**
     * @param non-empty-string $type
     * @param int<0, max> $passed
     * @param int<0, max> $expectedMin
     * @param int<0, max>|null $expectedMax
     */
    public static function fromTemplateArgumentsCount(
        string $type,
        int $passed,
        int $expectedMin,
        int $expectedMax,
        ?\Throwable $prev = null,
    ): self {
        $message = \vsprintf('Type "%s" expects %s template %s, but only %d were passed', [
            $type,
            StringInfo::formatRange($expectedMin, $expectedMax),
            $expectedMin === $expectedMax ? 'argument' : 'arguments',
            $passed,
        ]);

        return new static($message, previous: $prev);
    }
}
