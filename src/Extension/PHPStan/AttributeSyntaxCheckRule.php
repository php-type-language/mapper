<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Attribute as PhpAttribute;
use PhpParser\Node\Name as PhpName;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleError;
use TypeLang\Mapper\Extension\PHPStan\AttributeSyntaxCheckRule\AttributeTarget;

/**
 * @template-extends SyntaxCheckRule<PhpAttribute>
 */
abstract class AttributeSyntaxCheckRule extends SyntaxCheckRule
{
    /**
     * @var list<AttributeTarget>
     */
    private readonly array $declarations;

    public function __construct()
    {
        $this->declarations = \iterator_to_array($this->createAnalyzedDeclarations(), false);

        parent::__construct();
    }

    /**
     * @return \Traversable<array-key, AttributeTarget>
     */
    abstract protected function createAnalyzedDeclarations(): \Traversable;

    public function getNodeType(): string
    {
        return PhpAttribute::class;
    }

    /**
     * @return iterable<array-key, AttributeTarget>
     */
    private function getAllExpectedTargetsByAttribute(PhpAttribute $node): iterable
    {
        /** @phpstan-ignore-next-line : Additional assertion for AST compatibility */
        if (!$node->name instanceof PhpName) {
            return;
        }

        $attribute = $node->name->toString();

        // Check that this is the attribute we are looking for
        foreach ($this->declarations as $declaration) {
            if ($declaration->attribute === $attribute) {
                yield $declaration;
            }
        }
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach ($this->getAllExpectedTargetsByAttribute($node) as $target) {
            foreach ($this->process($target, $node, $scope) as $error) {
                $errors[] = $error;
            }
        }

        /** @var list<IdentifierRuleError> */
        return $errors;
    }

    /**
     * @return iterable<array-key, RuleError>
     */
    private function process(AttributeTarget $target, PhpAttribute $node, Scope $scope): iterable
    {
        // Check that the required argument is present
        $attributeDefinitionArgument = $node->args[$target->argument] ?? null;

        if ($attributeDefinitionArgument === null) {
            return;
        }

        // We can only parse constant strings
        $attributeDefinitionType = $scope->getType($attributeDefinitionArgument->value);

        foreach ($attributeDefinitionType->getConstantStrings() as $constantString) {
            $error = $this->checkSyntax($constantString->getValue());

            if ($error !== null) {
                yield $error;
            }
        }
    }
}
