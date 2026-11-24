<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

final class MethodStub
{
    private string $value = 'initial';

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = 'set:' . $value;

        return $this;
    }

    public function withOptional(string $value = 'default'): string
    {
        return $value;
    }

    public function find(string $key): string
    {
        return $key;
    }

    public function replace(string $key, string $value): void {}

    public function tag(string ...$tags): void {}

    public static function create(): self
    {
        return new self();
    }

    private function hidden(): string
    {
        return $this->value;
    }
}
