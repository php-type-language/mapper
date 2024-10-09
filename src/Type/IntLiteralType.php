<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class IntLiteralType extends IntType
{
    /**
     * @param numeric-string $name
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $name,
        private readonly int $value,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        $result = new IntLiteralNode($this->value, $this->name);

        if ($context->isDetailedTypes()) {
            return new NamedTypeNode(self::DEFAULT_TYPE_NAME, new TemplateArgumentsListNode([
                new TemplateArgumentNode($result),
            ]));
        }

        return $result;
    }

    #[\Override]
    public function match(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToInt($value);
        }

        return $value === $this->value;
    }

    #[\Override]
    public function cast(mixed $value, LocalContext $context): int
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToInt($value);
        }

        if ($value === $this->value) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
