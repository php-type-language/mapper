<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Literal\BoolLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class BoolLiteralType extends BoolType
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name,
        private readonly bool $value,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return parent::getTypeStatement($context);
        }

        $result = new BoolLiteralNode($this->value, $this->name);

        return new NamedTypeNode(self::DEFAULT_TYPE_NAME, new TemplateArgumentsListNode([
            new TemplateArgumentNode($result),
        ]));
    }

    #[\Override]
    public function match(mixed $value, LocalContext $context): bool
    {
        return $value === $this->value;
    }

    /**
     * Converts incoming value to the bool (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    #[\Override]
    public function cast(mixed $value, LocalContext $context): bool
    {
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
