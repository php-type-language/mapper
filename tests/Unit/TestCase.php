<?php

declare(strict_types=1);

namespace Serafim\Mapper\Tests\Unit;

use PHPUnit\Framework\Attributes\Group;
use Serafim\Mapper\Tests\TestCase as BaseTestCase;

#[Group('unit'), Group('serafim/mapper')]
abstract class TestCase extends BaseTestCase {}
