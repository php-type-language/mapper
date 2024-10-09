<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Mapping\Driver\AttributeDriver;
use TypeLang\Mapper\Mapping\Driver\DriverInterface;
use TypeLang\Mapper\Type;
use TypeLang\Mapper\Type\Builder;

class StandardPlatform implements PlatformInterface
{
    /**
     * @var list<GrammarFeature>
     */
    private const FEATURES_LIST = [
        GrammarFeature::Shapes,
        GrammarFeature::Literals,
        GrammarFeature::Generics,
        GrammarFeature::Union,
        GrammarFeature::List,
        GrammarFeature::Hints,
        GrammarFeature::Attributes,
    ];

    public function __construct(
        private readonly DriverInterface $driver = new AttributeDriver(),
    ) {}

    public function getName(): string
    {
        return 'standard';
    }

    public function getTypes(): iterable
    {
        // Adds support for the "mixed" type
        yield new Builder\MixedTypeBuilder('mixed');

        yield new Builder\NamedTypeBuilder('int', Type\IntType::class);

        // Adds support for the "bool" type
        yield new Builder\BoolTypeBuilder('bool');

        // Adds support for the "string" type
        yield new Builder\StringTypeBuilder('string');

        yield new Builder\NamedTypeBuilder('float', Type\FloatType::class);

        // Adds support for the "list<T>" type
        yield new Builder\ListTypeBuilder('list');

        // Adds support for the "array-key" type
        yield new Builder\ArrayKeyTypeBuilder('array-key');

        yield new Builder\NamedTypeBuilder('array', Type\ArrayType::class);

        yield new Builder\ObjectNamedTypeBuilder(\DateTimeInterface::class, Type\DateTimeType::class);

        yield new Builder\ObjectNamedTypeBuilder(\BackedEnum::class, Type\BackedEnumType::class);

        // Adds support for the "?T" statement
        yield new Builder\NullableTypeBuilder();

        // Adds support for the "null" literal and/or named type statement
        yield new Builder\NullTypeBuilder();

        // Adds support for the "true" and "false" literals
        yield new Builder\BoolLiteralTypeBuilder();

        // Adds support for the integer literal types
        yield new Builder\IntLiteralTypeBuilder();

        // Adds support for the "T[]" statement
        yield new Builder\TypesListBuilder();

        // Adds support for the "T|U" union types
        yield new Builder\UnionTypeBuilder();

        // Adds support for the "Path\To\Class" statement
        yield new Builder\ObjectTypeBuilder($this->driver);
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
