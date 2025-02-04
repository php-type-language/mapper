<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Builder;

use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Platform\Type\DateTimeType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, DateTimeType>
 */
class DateTimeTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && \is_a($statement->name->toLowerString(), \DateTimeInterface::class, true);
    }

    /**
     * @return class-string<\DateTime|\DateTimeImmutable>
     */
    private function getDateTimeClass(string $name): string
    {
        if ($name === \DateTimeInterface::class || \interface_exists($name)) {
            return \DateTimeImmutable::class;
        }

        /** @var class-string<\DateTime> */
        return $name;
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): DateTimeType {
        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsLessOrEqualThan($statement, 1, 0);

        if ($statement->arguments === null) {
            return new DateTimeType(
                class: $this->getDateTimeClass(
                    name: $statement->name->toString(),
                ),
            );
        }

        /** @var TemplateArgumentNode $formatArgument */
        $formatArgument = $statement->arguments->first();

        $this->expectNoTemplateArgumentHint($statement, $formatArgument);

        if (!$formatArgument->value instanceof StringLiteralNode) {
            throw InvalidTemplateArgumentException::becauseTemplateArgumentIsInvalid(
                expected: new NamedTypeNode('string'),
                argument: $formatArgument,
                type: $statement,
            );
        }

        return new DateTimeType(
            class: $this->getDateTimeClass(
                name: $statement->name->toString(),
            ),
            format: $formatArgument->value->value,
        );
    }
}
