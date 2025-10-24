<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class ParsedTypeInfo extends TypeInfo
{
    private static self $mixed;

    public function __construct(
        public readonly TypeStatement $statement,
        ?bool $strict = null,
        ?SourceInfo $source = null,
    ) {
        parent::__construct($strict, $source);
    }

    public static function mixed(): self
    {
        return self::$mixed ??= new self(
            statement: new NamedTypeNode('mixed'),
        );
    }
}
