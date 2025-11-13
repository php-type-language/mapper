<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\Concerns\InteractWithRuntimeContext;
use TypeLang\Mapper\Tests\TestCase;

#[Group('context')]
abstract class ContextTestCase extends TestCase
{
    use InteractWithRuntimeContext;
}
