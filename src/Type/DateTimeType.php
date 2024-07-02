<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;

final class DateTimeType extends AsymmetricLogicalType
{
    /**
     * @var class-string<\DateTime|\DateTimeImmutable>
     */
    private readonly string $class;

    /**
     * @param class-string<\DateTimeInterface> $name
     * @param non-empty-string $format
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        #[TargetTypeName]
        private readonly string $name,
        #[TargetTemplateArgument]
        private readonly string $format = \DateTimeInterface::RFC3339,
    ) {
        if (!\is_a($this->name, \DateTimeInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf(
                '%s must be a class that implements \DateTimeInterface',
                $this->name,
            ));
        }

        $this->class = match (true) {
            $this->name === \DateTimeInterface::class,
            \interface_exists($this->name) => \DateTimeImmutable::class,
            \is_a($this->name, \DateTime::class, true) => \DateTime::class,
            default => \DateTimeImmutable::class,
        };
    }

    protected function supportsNormalization(mixed $value, LocalContext $context): bool
    {
        return $value instanceof \DateTimeInterface;
    }

    /**
     * @throws InvalidValueException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): string
    {
        if (!$value instanceof \DateTimeInterface) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: \DateTimeInterface::class,
                actualValue: $value,
            );
        }

        return $value->format($this->format);
    }

    protected function supportsDenormalization(mixed $value, LocalContext $context): bool
    {
        if (!\is_string($value)) {
            return false;
        }

        try {
            return $this->parseDateTime($value, $context) !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @throws InvalidValueException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): \DateTimeInterface
    {
        if (!\is_string($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
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

        throw InvalidValueException::becauseInvalidValueGiven(
            context: $context,
            expectedType: 'string',
            actualValue: $value,
        );
    }

    private function parseDateTime(string $value, Context $context): ?\DateTimeInterface
    {
        if ($context->isStrictTypesEnabled()) {
            $result = ($this->class)::createFromFormat($this->format, $value);

            return \is_bool($result) ? null : $result;
        }

        return new $this->class($value);
    }
}
