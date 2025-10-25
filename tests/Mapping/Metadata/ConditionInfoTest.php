<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionInfo;

#[Group('meta')]
final class ConditionInfoTest extends MetadataTestCase
{
    public function testExpressionInfoConstruct(): void
    {
        $info = new ExpressionConditionInfo(expression: 'this.active', context: 'this');

        self::assertSame('this.active', $info->expression);
        self::assertSame('this', $info->context);
    }
}
