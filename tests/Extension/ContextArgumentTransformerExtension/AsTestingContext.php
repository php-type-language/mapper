<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsTestingContext
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public readonly string $name,
    ) {}
}
