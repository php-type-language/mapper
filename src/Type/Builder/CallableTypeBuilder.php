<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a new type from a callback.
 *
 * ```
 * // ExamplePlatform.php
 * yield new CallableTypeBuilder(
 *     names: 'custom-string',
 *     factory: static fn(): TypeInterface => new CustomString(),
 * );
 * ```
 *
 * @template TValue of mixed = mixed
 * @template-extends NamedTypeBuilder<TypeInterface<TValue>>
 */
class CallableTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param \Closure(): TypeInterface<TValue> $factory
     */
    public function __construct(
        array|string $names,
        protected readonly \Closure $factory,
    ) {
        parent::__construct($names);
    }

    public function build(TypeStatement $statement, BuildingContext $context): TypeInterface
    {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        try {
            return ($this->factory)();
        } catch (\Throwable $e) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: 'An error occurred while trying to fetch {{type}} type from callback',
                previous: $e,
            );
        }
    }
}
