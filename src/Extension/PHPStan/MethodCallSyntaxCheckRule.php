<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Arg as PhpFunctionArgument;
use PhpParser\Node\Expr\MethodCall as PhpMethodCall;
use PhpParser\Node\Identifier as PhpIdentifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;
use TypeLang\Mapper\Extension\PHPStan\MethodCallSyntaxCheckRule\MethodCallTarget;

/**
 * @template-extends SyntaxCheckRule<PhpMethodCall>
 */
abstract class MethodCallSyntaxCheckRule extends SyntaxCheckRule
{
    /**
     * @var list<MethodCallTarget>
     */
    private readonly array $declarations;

    public function __construct()
    {
        $this->declarations = \iterator_to_array($this->createAnalyzedDeclarations(), false);

        parent::__construct();
    }

    /**
     * @return \Traversable<array-key, MethodCallTarget>
     */
    abstract protected function createAnalyzedDeclarations(): \Traversable;

    public function getNodeType(): string
    {
        return PhpMethodCall::class;
    }

    /**
     * @return iterable<array-key, MethodCallTarget>
     */
    private function getAllExpectedTargetsByMethodName(PhpMethodCall $node): iterable
    {
        if (!$node->name instanceof PhpIdentifier) {
            return;
        }

        $method = $node->name->toString();

        // Check that this is the method we are looking for
        foreach ($this->declarations as $declaration) {
            if ($declaration->method === $method) {
                yield $declaration;
            }
        }
    }

    /**
     * @throws ShouldNotHappenException
     */
    private function findExpectedTarget(PhpMethodCall $node, Scope $scope): ?MethodCallTarget
    {
        // Check that the call is of the required type
        $methodInvocationType = $scope->getType($node->var);

        foreach ($this->getAllExpectedTargetsByMethodName($node) as $target) {
            if ($target->type->isSuperTypeOf($methodInvocationType)->yes()) {
                return $target;
            }
        }

        return null;
    }

    /**
     * @return iterable<array-key, string>
     * @throws ShouldNotHappenException
     */
    private function findTypeArgument(PhpMethodCall $node, Scope $scope): iterable
    {
        $target = $this->findExpectedTarget($node, $scope);

        if ($target === null) {
            return null;
        }

        // Check that the required argument is present
        $methodInvocationArgument = $node->args[$target->argument] ?? null;

        if (!$methodInvocationArgument instanceof PhpFunctionArgument) {
            return;
        }

        // We can only parse constant strings
        $methodInvocationType = $scope->getType($methodInvocationArgument->value);

        foreach ($methodInvocationType->getConstantStrings() as $constantStringType) {
            yield $constantStringType->getValue();
        }
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach ($this->findTypeArgument($node, $scope) as $expression) {
            $error = $this->checkSyntax($expression);

            if ($error !== null) {
                $errors[] = $error;
            }
        }

        /** @var list<IdentifierRuleError> */
        return $errors;
    }
}
