<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

final class RawTypeInfo extends TypeInfo
{
    private static self $mixed;

    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $definition,
        ?SourceInfo $source = null,
    ) {
        parent::__construct($source);
    }

    public static function mixed(): self
    {
        return self::$mixed ??= new self('mixed');
    }
}
