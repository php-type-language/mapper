<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionMetadata;

#[Group('meta')]
final class ConditionMetadataTest extends MetadataTestCase
{
    public function testNullCondition(): void
    {
        $cond = new NullConditionMetadata();

        self::assertTrue($cond->match(new \stdClass(), null));
        self::assertFalse($cond->match(new \stdClass(), 0));
    }

    public function testEmptyCondition(): void
    {
        $cond = new EmptyConditionMetadata();

        self::assertTrue($cond->match(new \stdClass(), null));
        self::assertTrue($cond->match(new \stdClass(), 0));
        self::assertTrue($cond->match(new \stdClass(), ''));
        self::assertFalse($cond->match(new \stdClass(), 'x'));
    }

    public function testExpressionCondition(): void
    {
        $lang = new ExpressionLanguage();

        $parsed = $lang->parse('this.enabled == true', ['this']);
        $cond = new ExpressionConditionMetadata(expression: $parsed);

        $obj = (object) ['enabled' => true];
        self::assertTrue($cond->match($obj, 'anything'));

        $obj = (object) ['enabled' => false];
        self::assertFalse($cond->match($obj, 'anything'));
    }
}
