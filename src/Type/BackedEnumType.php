<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Literal\IntLiteralNode;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

class BackedEnumType extends AsymmetricType
{
    /**
     * @param class-string<\BackedEnum> $name
     */
    public function __construct(
        private readonly string $name,
        private readonly TypeInterface $type,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if ($context->isNormalization()) {
            return new NamedTypeNode($this->name);
        }

        $cases = [];

        foreach ($this->name::cases() as $case) {
            $cases[] = match (true) {
                \is_string($case->value) => StringLiteralNode::createFromValue($case->value),
                \is_int($case->value) => new IntLiteralNode($case->value),
            };
        }

        return match (\count($cases)) {
            // The number of cases cannot be zero, so
            // this will most likely not be possible.
            0 => new NamedTypeNode('never'),
            1 => $cases[0],
            default => new UnionTypeNode(...$cases),
        };
    }

    protected function isNormalizable(mixed $value, LocalContext $context): bool
    {
        return $value instanceof $this->name;
    }

    /**
     * Converts enum case (of {@see \BackedEnum}) objects to their
     * scalar representation.
     *
     * @throws InvalidValueException
     */
    public function normalize(mixed $value, LocalContext $context): int|string
    {
        if (!$value instanceof $this->name) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        return $value->value;
    }

    protected function isDenormalizable(mixed $value, LocalContext $context): bool
    {
        $isSupportsType = $this->type->match($value, $context);

        if (!$isSupportsType) {
            return false;
        }

        /** @var int|string $denormalized */
        $denormalized = $this->type->cast($value, $context);

        try {
            return ($this->name)::tryFrom($denormalized) !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Converts a scalar representation of an enum to an enum case object.
     *
     * @throws InvalidValueException
     */
    public function denormalize(mixed $value, LocalContext $context): \BackedEnum
    {
        $denormalized = $this->type->cast($value, $context);

        if (!\is_string($denormalized) && !\is_int($denormalized)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        try {
            $case = $this->name::tryFrom($denormalized);
        } catch (\TypeError) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        return $case ?? throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
