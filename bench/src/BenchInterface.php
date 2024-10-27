<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench;

interface BenchInterface
{
    public function benchTypeLangWithDocBlocks(): void;
    public function benchTypeLangWithAttributes(): void;
    public function benchJmsWithAttributes(): void;
    public function benchValinorWithPhpStan(): void;
    public function benchSymfonyWithPhpStan(): void;
    public function benchSymfonyWithDocBlock(): void;
}
