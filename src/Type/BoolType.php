<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class BoolType implements TypeInterface
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'bool';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name = self::DEFAULT_TYPE_NAME,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode($this->name);
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return \is_bool($value);
    }

    /**
     * Converts incoming value to the bool (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->castToBoolIfPossible($value);
        }

        if (!\is_bool($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: 'bool',
                context: $context,
            );
        }

        return $value;
    }

    /**
     * A method to convert input data to a bool representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    private function castToBoolIfPossible(mixed $value): bool
    {
        return match (true) {
            \is_array($value) => $value !== [],
            \is_object($value) => true,
            \is_string($value) => $value !== '',
            default => (bool) $value,
        };
    }
}
