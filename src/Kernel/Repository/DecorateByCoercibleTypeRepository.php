<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository;

use TypeLang\Mapper\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Kernel\Repository\TypeDecorator\CoercibleType;
use TypeLang\Mapper\Kernel\Repository\TypeDecorator\TypeDecorator;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class DecorateByCoercibleTypeRepository extends TypeRepositoryDecorator
{
    /**
     * @var array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    private array $coercers = [];

    /**
     * @param iterable<TypeCoercerInterface, list<class-string<TypeInterface>>> $coercers
     */
    public function __construct(
        TypeRepositoryInterface $delegate,
        iterable $coercers,
    ) {
        parent::__construct($delegate);

        $this->coercers = $this->formatTypeCoercers($coercers);
    }

    /**
     * @param iterable<TypeCoercerInterface, list<class-string<TypeInterface>>> $coercers
     *
     * @return array<class-string<TypeInterface>, TypeCoercerInterface>
     */
    private function formatTypeCoercers(iterable $coercers): array
    {
        $result = [];

        foreach ($coercers as $coercer => $types) {
            foreach ($types as $type) {
                $result[$type] = $coercer;
            }
        }

        return $result;
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        $concrete = $type = parent::getTypeByStatement($statement);

        if ($type instanceof CoercibleType) {
            return $type;
        }

        if ($concrete instanceof TypeDecorator) {
            $concrete = $concrete->getDecoratedType();
        }

        $coercer = $this->coercers[$concrete::class] ?? null;

        if ($coercer === null) {
            return $type;
        }

        return new CoercibleType($coercer, $type);
    }
}
