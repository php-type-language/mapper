<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Check;

/**
 * String has no uppercase ASCII letters.
 */
final class LowercaseCheck implements CheckInterface
{
    /**
     * @var non-empty-string
     */
    public readonly string $description;

    public function __construct()
    {
        $this->description = 'lowercase';
    }

    public function isValid(mixed $value): bool
    {
        return \is_string($value) && \strpbrk($value, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') === false;
    }
}
