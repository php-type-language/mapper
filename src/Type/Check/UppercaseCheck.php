<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * String has no lowercase ASCII letters.
 */
final class UppercaseCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct()
    {
        $this->description = 'uppercase';
    }

    public function isValid(mixed $value): bool
    {
        return \is_string($value) && \strpbrk($value, 'abcdefghijklmnopqrstuvwxyz') === false;
    }
}
