<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Literal\BoolLiteralNode;

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
    public function getTypeStatement(LocalContext $context): BoolLiteralNode
    {
        return new BoolLiteralNode($this->value, $this->name);
    }

    #[\Override]
    public function match(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToBool($value);
        }

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
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->tryCastToBool($value);
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
