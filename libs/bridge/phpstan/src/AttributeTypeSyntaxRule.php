<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use TypeLang\Mapper\Mapping\MapType;

/**
 * Reports a type written wrong in a mapping attribute.
 *
 * ```
 * #[MapType('list<int')] // error
 * public array $items;
 * ```
 *
 * @implements Rule<Attribute>
 */
final class AttributeTypeSyntaxRule implements Rule
{
    public function __construct(
        private readonly TypeSyntaxChecker $checker = new TypeSyntaxChecker(),
    ) {}

    public function getNodeType(): string
    {
        return Attribute::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (\strcasecmp(\ltrim($node->name->toString(), '\\'), MapType::class) !== 0) {
            return [];
        }

        $argument = $this->checker->findArgument($node->args, 'type', 0);

        if ($argument === null) {
            return [];
        }

        return $this->checker->check($argument, $scope);
    }
}
