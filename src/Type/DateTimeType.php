<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Context\Context;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class DateTimeType extends AsymmetricType
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

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode(
            name: $this->name,
            arguments: new TemplateArgumentsListNode([
                new TemplateArgumentNode(new StringLiteralNode(
                    value: $this->format,
                    raw: \sprintf('"%s"', \addcslashes($this->format, '"')),
                )),
            ])
        );
    }

    protected function isNormalizable(mixed $value, LocalContext $context): bool
    {
        return $value instanceof \DateTimeInterface;
    }

    /**
     * @throws InvalidValueException
     */
    public function normalize(mixed $value, LocalContext $context): string
    {
        if (!$value instanceof \DateTimeInterface) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: \DateTimeInterface::class,
                context: $context,
            );
        }

        return $value->format($this->format);
    }

    protected function isDenormalizable(mixed $value, LocalContext $context): bool
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
    public function denormalize(mixed $value, LocalContext $context): \DateTimeInterface
    {
        if (!\is_string($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: 'string',
                context: $context,
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
            value: $value,
            expected: 'string',
            context: $context,
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
