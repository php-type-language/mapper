<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Bench;

interface BenchInterface
{
    public function benchTypeLangDocBlock(): void;
    public function benchTypeLangAttributes(): void;
    public function benchJms(): void;
    public function benchValinor(): void;
    public function benchSymfonyPhpStan(): void;
    public function benchSymfonyDocBlock(): void;
}
