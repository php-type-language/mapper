<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;

/**
 * @template-implements TypeInterface<\DateTimeInterface, non-empty-string>
 */
final class DateTimeType implements TypeInterface
{
    /**
     * @param class-string<\DateTimeInterface> $name
     * @param non-empty-string $format
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name,
        #[TargetTemplateArgument]
        private readonly string $format = \DateTimeInterface::RFC3339,
    ) {}

    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): string
    {
        if (!$value instanceof \DateTimeInterface) {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: \DateTimeInterface::class,
                actualValue: $value,
            );
        }

        return $value->format($this->format);
    }

    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): \DateTimeInterface
    {
        if (!\is_string($value)) {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: 'string',
                actualValue: $value,
            );
        }

        try {
            $result = $this->parseDateTime($value, $context);

            if ($result instanceof \DateTimeInterface) {
                return $result;
            }
        } catch (\Throwable) {
        }

        throw InvalidValueException::becauseInvalidValue(
            context: $context,
            expectedType: 'string',
            actualValue: $value,
        );
    }

    private function parseDateTime(string $value, Context $context): ?\DateTimeInterface
    {
        if ($context->isStrictTypesEnabled()) {
            $result = ($this->name)::createFromFormat(
                format: $this->format,
                datetime: $value,
            );

            return \is_bool($result) ? null : $result;
        }

        return new $this->name($value);
    }
}
