<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;
use TypeLang\Mapper\DenormalizerInterface;
use TypeLang\Mapper\NormalizerInterface;

/**
 * Reports a type written wrong in a constant string given to a mapper.
 *
 * ```
 * $mapper->denormalize($data, 'list<int'); // error
 * ```
 *
 * @implements Rule<MethodCall>
 */
final class MethodCallTypeSyntaxRule implements Rule
{
    /**
     * Classes by the names of their methods taking the type as the second
     * argument, named `type`
     */
    private const METHODS = [
        'denormalize' => DenormalizerInterface::class,
        'isdenormalizable' => DenormalizerInterface::class,
        'normalize' => NormalizerInterface::class,
        'isnormalizable' => NormalizerInterface::class,
    ];

    public function __construct(
        private readonly TypeSyntaxChecker $checker = new TypeSyntaxChecker(),
    ) {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $class = self::METHODS[$node->name->toLowerString()] ?? null;

        if ($class === null || !(new ObjectType($class))->isSuperTypeOf($scope->getType($node->var))->yes()) {
            return [];
        }

        $argument = $this->checker->findArgument($node->args, 'type', 1);

        if ($argument === null) {
            return [];
        }

        return $this->checker->check($argument, $scope);
    }
}
