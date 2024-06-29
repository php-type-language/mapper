<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Functional;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\TestCase as BaseTestCase;

#[Group('functional'), Group('type-lang/mapper')]
abstract class TestCase extends BaseTestCase {}
