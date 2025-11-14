<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\PHPStan;

use TypeLang\Mapper\Extension\PHPStan\MethodCallSyntaxCheckRule\MethodCallTarget;
use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\DenormalizerInterface;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\NormalizerInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;

final class MapperMethodCallSyntaxRule extends MethodCallSyntaxCheckRule
{
    /**
     * @return \Traversable<array-key, MethodCallTarget>
     */
    protected function createAnalyzedDeclarations(): \Traversable
    {
        // Mapper
        yield new MethodCallTarget(DenormalizerInterface::class, 'denormalize', 1);
        yield new MethodCallTarget(DenormalizerInterface::class, 'isDenormalizable', 1);
        yield new MethodCallTarget(NormalizerInterface::class, 'normalize', 1);
        yield new MethodCallTarget(NormalizerInterface::class, 'isNormalizable', 1);
        yield new MethodCallTarget(Mapper::class, 'map', 2);
        yield new MethodCallTarget(Mapper::class, 'canMap', 2);

        // Mapper's components
        yield new MethodCallTarget(TypeParserInterface::class, 'getStatementByDefinition', 0);

        // Mapper's contexts
        yield new MethodCallTarget(BuildingContext::class, 'getTypeByDefinition', 0);
        yield new MethodCallTarget(BuildingContext::class, 'getStatementByDefinition', 0);
        yield new MethodCallTarget(RuntimeContext::class, 'getTypeByDefinition', 0);
        yield new MethodCallTarget(RuntimeContext::class, 'getStatementByDefinition', 0);

        // Mapper's metadata
        yield new MethodCallTarget(MapType::class, '__construct', 0);
    }
}
