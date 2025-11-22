<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Type\DateTimeType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TDate of \DateTimeImmutable|\DateTime = \DateTimeImmutable
 * @template TResult of string = string
 *
 * @template-extends Builder<NamedTypeNode, TypeInterface<TResult|TDate>>
 */
class DateTimeTypeBuilder extends Builder
{
    private const DEFAULT_DATE_INTERFACE_MAPPING = [
        \DateTimeInterface::class => \DateTimeImmutable::class,
        /** @phpstan-ignore-next-line : Classes and interfaces may be missing */
        \Carbon\CarbonInterface::class => \Carbon\CarbonImmutable::class,
    ];

    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_INNER_SCALAR_TYPE = 'string';

    public function __construct(
        /**
         * @var non-empty-string
         */
        protected readonly string $type = self::DEFAULT_INNER_SCALAR_TYPE,
        /**
         * @var array<class-string<\DateTimeInterface>, class-string<\DateTimeInterface>>
         *
         * @phpstan-ignore-next-line : PHPStan false-positive (?)
         */
        protected readonly array $dateInterfaceMapping = self::DEFAULT_DATE_INTERFACE_MAPPING,
    ) {}

    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof NamedTypeNode
            && \is_a($stmt->name->toLowerString(), \DateTimeInterface::class, true);
    }

    public function build(TypeStatement $stmt, BuildingContext $context): DateTimeType
    {
        $this->expectNoShapeFields($stmt);
        $this->expectTemplateArgumentsLessOrEqualThan($stmt, 1);

        /** @var DateTimeType<TDate> */
        return new DateTimeType(
            class: $this->getDateTimeClass($stmt),
            format: $this->findDateTimeFormat($stmt),
            input: $context->getTypeByDefinition($this->type),
        );
    }

    /**
     * @return non-empty-string|null
     */
    private function findDateTimeFormat(NamedTypeNode $stmt): ?string
    {
        if ($stmt->arguments === null) {
            return null;
        }

        /** @var TemplateArgumentNode $formatArgument */
        $formatArgument = $stmt->arguments->first();

        $this->expectNoTemplateArgumentHint($stmt, $formatArgument);

        if (!$formatArgument->value instanceof StringLiteralNode) {
            throw InvalidTemplateArgumentException::becauseTemplateArgumentMustBe(
                argument: $formatArgument,
                expected: new NamedTypeNode('string'),
                type: $stmt,
            );
        }

        $formatValue = $formatArgument->value->value;

        if ($formatValue === '') {
            throw InvalidTemplateArgumentException::becauseTemplateArgumentMustBe(
                argument: $formatArgument,
                expected: new NamedTypeNode('non-empty-string'),
                type: $stmt,
            );
        }

        return $formatValue;
    }

    /**
     * @return class-string<TDate>
     */
    private function getDateTimeClass(NamedTypeNode $stmt): string
    {
        /** @var class-string<TDate> $class */
        $class = $stmt->name->toString();

        if (!\interface_exists($class)) {
            return $class;
        }

        foreach ($this->dateInterfaceMapping as $interface => $impl) {
            if (\is_a($interface, $class, true)) {
                $class = $impl;
                break;
            }
        }

        /** @var class-string<TDate> */
        return $class;
    }
}
