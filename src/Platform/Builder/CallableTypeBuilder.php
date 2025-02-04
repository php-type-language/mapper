<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Builder;

use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
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
 * @template-extends NamedTypeBuilder<TypeInterface>
 */
class CallableTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param \Closure(): TypeInterface $factory
     */
    public function __construct(
        array|string $names,
        protected readonly \Closure $factory,
    ) {
        parent::__construct($names);
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
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
