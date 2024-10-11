<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Type\ClassStringType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<ClassStringType>
 */
class ClassStringTypeBuilder extends NamedTypeBuilder
{
    public function build(TypeStatement $statement, RepositoryInterface $types): ClassStringType
    {
        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsLessOrEqualThan($statement, 1);

        if ($statement->arguments === null) {
            return new ClassStringType($statement->name->toString());
        }

        /** @var TemplateArgumentNode $inner */
        $inner = $statement->arguments->first();

        $this->expectNoTemplateArgumentHint($statement, $inner);

        if ($inner->value instanceof NamedTypeNode) {
            return new ClassStringType(
                name: $statement->name->toString(),
                class: $inner->value->name->toString(),
            );
        }

        if ($inner->value instanceof StringLiteralNode) {
            return new ClassStringType(
                name: $statement->name->toString(),
                // @phpstan-ignore-next-line
                class: $inner->value->getValue(),
            );
        }

        throw InvalidTemplateArgumentException::becauseTemplateArgumentIsInvalid(
            expected: new NamedTypeNode('string'),
            argument: $inner,
            type: $statement,
        );
    }
}
