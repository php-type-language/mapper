<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Repository\TypeDecorator\CoercibleType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

use function TypeLang\Mapper\iterable_to_array;

final class DecorateByCoercibleTypeRepository extends TypeRepositoryDecorator
{
    /**
     * @var array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    private array $coercers = [];

    /**
     * @param iterable<class-string<TypeInterface>, TypeCoercerInterface> $coercers
     */
    public function __construct(
        TypeRepositoryInterface $delegate,
        iterable $coercers,
    ) {
        parent::__construct($delegate);

        $this->coercers = iterable_to_array($coercers);
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        $type = parent::getTypeByStatement($statement);

        if ($type instanceof CoercibleType) {
            return $type;
        }

        $coercer = $this->coercers[$type::class] ?? null;

        if ($coercer === null) {
            return $type;
        }

        return new CoercibleType($coercer, $type);
    }
}
