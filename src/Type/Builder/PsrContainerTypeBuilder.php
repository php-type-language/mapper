<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use Psr\Container\ContainerInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<TypeInterface>
 */
class PsrContainerTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param class-string<TypeInterface>                        $serviceId
     */
    public function __construct(
        array|string $names,
        protected readonly string $serviceId,
        protected readonly ContainerInterface $container,
    ) {
        parent::__construct($names);
    }

    public function build(TypeStatement $statement, TypeRepositoryInterface $types, TypeParserInterface $parser): TypeInterface
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        $service = $this->container->get($this->serviceId);

        if (!$service instanceof TypeInterface) {
            throw new \RuntimeException("Type service '{$this->serviceId}' does not implement TypeLang\Mapper\Type\TypeInterface");
        }

        return $service;
    }
}
