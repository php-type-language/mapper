<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\ExpressionLanguage\Node\ArgumentsNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\GetAttrNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Mapping\Metadata\ExpressionConditionMetadata;

#[CoversClass(ExpressionConditionMetadata::class)]
final class ExpressionConditionMetadataTest extends MetadataTestCase
{
    private function createParsedExpressionAlwaysTrue(string $context = ExpressionConditionMetadata::DEFAULT_CONTEXT_VARIABLE_NAME): ParsedExpression
    {
        $root = new ConstantNode(true);
        return new ParsedExpression('true', $root);
    }

    private function createParsedExpressionCheckContext(string $property, string $context = ExpressionConditionMetadata::DEFAULT_CONTEXT_VARIABLE_NAME): ParsedExpression
    {
        $name = new NameNode($context);
        $attr = new GetAttrNode($name, new ConstantNode($property), new ArgumentsNode());
        return new ParsedExpression($context . '.' . $property, $attr);
    }

    public function testGetters(): void
    {
        $expr = $this->createParsedExpressionAlwaysTrue('ctx');
        $m = new ExpressionConditionMetadata($expr, 'ctx', 5);
        self::assertSame($expr, $m->getExpression());
        self::assertSame('ctx', $m->getContextVariableName());
        self::assertSame(5, $m->getTimestamp());
    }

    public function testMatchUsesExpressionNodesEvaluation(): void
    {
        $expr = $this->createParsedExpressionAlwaysTrue();
        $m = new ExpressionConditionMetadata($expr);
        self::assertTrue($m->match(new \stdClass(), null));
    }
}


