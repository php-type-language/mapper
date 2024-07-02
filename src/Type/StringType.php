<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class StringType implements LogicalTypeInterface
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_TYPE_NAME = 'string';

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
        return \is_string($value);
    }

    /**
     * Converts incoming value to the string (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): string
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->castToStringIfPossible($value);
        }

        if (!\is_string($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: $this->getTypeStatement($context),
                actualValue: $value,
            );
        }

        return $value;
    }

    /**
     * A method to convert input data to a string representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    private function castToStringIfPossible(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            $value instanceof \Stringable => (string) $value,
            \is_array($value),
            \is_object($value) => $value,
            $value === true => '1',
            $value === false => '0',
            // @phpstan-ignore-next-line : Any other type can be converted to string
            default => (string) $value,
        };
    }
}
