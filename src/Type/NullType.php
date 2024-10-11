<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Literal\NullLiteralNode;

class NullType implements TypeInterface
{
    public function getTypeStatement(LocalContext $context): NullLiteralNode
    {
        return new NullLiteralNode();
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return $value === null;
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): mixed
    {
        if ($this->match($value, $context)) {
            return null;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
