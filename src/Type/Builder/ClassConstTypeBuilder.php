<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\InternalTypeException;
use TypeLang\Mapper\Type\ClassConstType;
use TypeLang\Parser\Node\Stmt\ClassConstNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<ClassConstNode, ClassConstType>
 */
final class ClassConstTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof ClassConstNode;
    }

    public function build(TypeStatement $stmt, BuildingContext $context): ClassConstType
    {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($stmt instanceof ClassConstNode);

        /** @var class-string $class */
        $class = $stmt->class->toString();
        /** @phpstan-ignore-next-line : Constant name is always present */
        $constant = $stmt->constant->toString();

        try {
            $reflection = new \ReflectionClassConstant($class, $constant);
        } catch (\Throwable $e) {
            throw InternalTypeException::becauseInternalTypeErrorOccurs(
                type: $stmt,
                message: 'The type of a class constant {{type}} cannot be determined',
                previous: $e,
            );
        }

        return new ClassConstType(
            value: $reflection->getValue(),
            type: $context->getTypeByValue($reflection->getValue()),
        );
    }
}
