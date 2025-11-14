<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\PHPStan;

use TypeLang\Mapper\Extension\PHPStan\AttributeSyntaxCheckRule\AttributeTarget;
use TypeLang\Mapper\Mapping\MapType;

final class MetadataAttributeSyntaxCheckRule extends AttributeSyntaxCheckRule
{
    protected function createAnalyzedDeclarations(): \Traversable
    {
        yield new AttributeTarget(MapType::class, 0);
    }
}
