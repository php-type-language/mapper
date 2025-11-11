<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Tests\Concerns\InteractWithAssertions;
use TypeLang\Mapper\Tests\Concerns\InteractWithTypeParser;
use TypeLang\Mapper\Tests\TestCase;

#[Group('exception')]
abstract class ExceptionTestCase extends TestCase
{
    use InteractWithTypeParser;
    use InteractWithAssertions;
}
