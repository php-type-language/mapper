<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<TypeInterface>
 */
class CallableTypeBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param \Closure(): TypeInterface                          $factory
     */
    public function __construct(
        array|string $names,
        protected readonly \Closure $factory,
    ) {
        parent::__construct($names);
    }

    public function build(TypeStatement $statement, TypeRepositoryInterface $types, TypeParserInterface $parser): TypeInterface
    {
        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return ($this->factory)();
    }
}
