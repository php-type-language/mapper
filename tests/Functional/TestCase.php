<?php

declare(strict_types=1);

namespace Serafim\Mapper\Tests\Functional;

use PHPUnit\Framework\Attributes\Group;
use Serafim\Mapper\Tests\TestCase as BaseTestCase;

#[Group('functional'), Group('serafim/mapper')]
abstract class TestCase extends BaseTestCase {}
