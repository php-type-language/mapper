<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder\ConstFinder;
use TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder\ConstFinderMode;
use TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder\ConstGroupCreator;
use TypeLang\Mapper\Type\UnionConstType;
use TypeLang\Parser\Node\Stmt\ConstMaskNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<ConstMaskNode, UnionConstType<mixed, mixed>>
 */
final class ConstMaskTypeBuilder implements TypeBuilderInterface
{
    private readonly ConstFinder $finder;
    private readonly ConstGroupCreator $groups;

    public function __construct()
    {
        $this->finder = new ConstFinder();
        $this->groups = new ConstGroupCreator();
    }

    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof ConstMaskNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): UnionConstType
    {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($stmt instanceof ConstMaskNode);

        return new UnionConstType(
            groups: $this->groups->create(
                values: $this->getConstants($stmt),
                context: $context,
            ),
        );
    }

    /**
     * @return list<mixed>
     */
    private function getConstants(ConstMaskNode $stmt): array
    {
        return $this->finder->find(
            constants: \get_defined_constants(),
            mask: $stmt->name,
            mode: ConstFinderMode::Prefix,
        );
    }
}
