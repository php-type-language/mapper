<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\Context;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class DateTimeType extends AsymmetricType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_DATETIME_FORMAT = \DateTimeInterface::RFC3339;

    /**
     * @param non-empty-string $name
     * @param class-string<\DateTime|\DateTimeImmutable> $class
     */
    public function __construct(
        protected readonly string $name,
        protected readonly string $class,
        protected readonly ?string $format = null,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if ($this->format === null) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode($this->name, new TemplateArgumentsListNode([
            new TemplateArgumentNode(StringLiteralNode::createFromValue($this->format)),
        ]));
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

        return $value->format($this->format ?? self::DEFAULT_DATETIME_FORMAT);
    }

    protected function isDenormalizable(mixed $value, LocalContext $context): bool
    {
        if (!\is_string($value)) {
            return false;
        }

        try {
            return $this->tryParseDateTime($value, $context) !== null;
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

        $result = $this->tryParseDateTime($value, $context);

        if ($result instanceof \DateTimeInterface) {
            return $result;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }

    private function tryParseDateTime(string $value, Context $context): ?\DateTimeInterface
    {
        if ($this->format !== null) {
            try {
                $result = ($this->class)::createFromFormat($this->format, $value);
            } catch (\Throwable) {
                return null;
            }

            return \is_bool($result) ? null : $result;
        }

        return new $this->class($value);
    }
}
