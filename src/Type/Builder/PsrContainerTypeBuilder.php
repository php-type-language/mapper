<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use Psr\Container\ContainerInterface;
use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a new type from a PSR-compatible container using service-location.
 *
 * ```
 * // ExamplePlatform.php
 * yield new CallableTypeBuilder(
 *     names: 'custom-string',
 *     serviceId: CustomString::class,
 *     container: $psrCompatibleContainer,
 * );
 * ```
 *
 * Tip: When using Symfony, make sure the service is not private.
 *
 * @template-extends NamedTypeBuilder<TypeInterface>
 */
class PsrContainerTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param class-string<TypeInterface> $serviceId
     */
    public function __construct(
        array|string $names,
        protected readonly string $serviceId,
        protected readonly ContainerInterface $container,
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
            $service = $this->container->get($this->serviceId);
        } catch (\Throwable $e) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: 'An error occurred while trying to fetch {{type}} type from service container',
                previous: $e,
            );
        }

        if (!$service instanceof TypeInterface) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $statement,
                message: \sprintf(
                    'Received service from service container defined as {{type}} must be instanceof %s, but %s given',
                    TypeInterface::class,
                    \get_debug_type($service),
                ),
            );
        }

        return $service;
    }
}
