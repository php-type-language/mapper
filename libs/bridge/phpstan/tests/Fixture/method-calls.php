<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\PHPStan\Tests\Fixture;

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\NormalizerInterface;

function checkMethodCalls(Mapper $mapper, NormalizerInterface $normalizer, object $other, string $unknown): void
{
    $mapper->denormalize([], 'list<int>');
    $mapper->denormalize([], 'list<int');
    $mapper->isDenormalizable(type: 'array{a: int', value: []);
    $mapper->normalize([]);
    $normalizer->normalize([], 'int|');
    $normalizer->isNormalizable([], $unknown);
    $mapper->denormalize([], \rand(0, 1) === 1 ? 'int' : 'int<');

    $other->denormalize([], 'list<int');
}
