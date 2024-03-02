<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\TestCase as BaseTestCase;

#[Group('unit'), Group('type-lang/mapper')]
abstract class TestCase extends BaseTestCase {}
