<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Mapping\Metadata\ExpressionConditionMetadata;

#[CoversClass(ExpressionConditionMetadata::class)]
final class ExpressionConditionMetadataTest extends MetadataTestCase
{
    private function createParsedExpressionAlwaysTrue(): ParsedExpression
    {
        $root = new ConstantNode(true);

        return new ParsedExpression('true', $root);
    }

    public function testGetters(): void
    {
        $expr = $this->createParsedExpressionAlwaysTrue();
        $m = new ExpressionConditionMetadata($expr, 'ctx', 5);

        self::assertSame($expr, $m->expression);
        self::assertSame('ctx', $m->variable);
        self::assertSame(5, $m->timestamp);
    }

    public function testMatchUsesExpressionNodesEvaluation(): void
    {
        $expr = $this->createParsedExpressionAlwaysTrue();
        $m = new ExpressionConditionMetadata($expr);

        self::assertTrue($m->match(new \stdClass(), null));
    }
}
