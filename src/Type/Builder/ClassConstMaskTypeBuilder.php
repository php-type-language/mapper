<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder\ConstFinder;
use TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder\ConstFinderMode;
use TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder\ConstGroupCreator;
use TypeLang\Mapper\Type\UnionConstType;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\ClassConstMaskNode;
use TypeLang\Parser\Node\Stmt\ClassConstNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<ClassConstMaskNode, UnionConstType<mixed, mixed>>
 */
final class ClassConstMaskTypeBuilder implements TypeBuilderInterface
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
        return $stmt instanceof ClassConstMaskNode
            && !$stmt instanceof ClassConstNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): UnionConstType
    {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($stmt instanceof ClassConstMaskNode);

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
    private function getConstants(ClassConstMaskNode $stmt): array
    {
        $constants = $this->getAllConstants($stmt);

        if ($stmt->constant === null) {
            return \array_values($constants);
        }

        return $this->finder->find(
            constants: $constants,
            mask: new Name($stmt->constant),
            mode: ConstFinderMode::Prefix,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getAllConstants(ClassConstMaskNode $stmt): array
    {
        /** @var class-string $class */
        $class = $stmt->class->toString();

        try {
            return (new \ReflectionClass($class))
                ->getConstants();
        } catch (\ReflectionException $e) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $stmt,
                message: 'The type of a class constant {{type}} cannot be determined',
                previous: $e,
            );
        }
    }
}
