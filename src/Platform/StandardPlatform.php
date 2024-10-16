<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Type;
use TypeLang\Mapper\Type\Builder;

class StandardPlatform extends Platform
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

    public function getName(): string
    {
        return 'standard';
    }

    public function getTypes(): iterable
    {
        // Adds support for the "mixed" type
        yield new Builder\SimpleTypeBuilder('mixed', Type\MixedType::class);

        // Adds support for the "bool" type
        yield new Builder\SimpleTypeBuilder('bool', Type\BoolType::class);

        // Adds support for the "string" type
        yield new Builder\SimpleTypeBuilder('string', Type\StringType::class);

        // Adds support for the "non-empty-string" type
        yield new Builder\SimpleTypeBuilder('non-empty-string', Type\NonEmptyStringType::class);

        // Adds support for the "numeric-string" type
        yield new Builder\SimpleTypeBuilder('numeric-string', Type\NumericStringType::class);

        // Adds support for the "class-string" type
        yield new Builder\ClassStringTypeBuilder('class-string');

        // Adds support for the "int" type
        yield new Builder\IntTypeBuilder('int');

        // Additional int ranges
        yield new Builder\IntRangeTypeBuilder('positive-int', 1, Type\IntType::DEFAULT_INT_MAX);
        yield new Builder\IntRangeTypeBuilder('non-negative-int', 0, Type\IntType::DEFAULT_INT_MAX);
        yield new Builder\IntRangeTypeBuilder('negative-int', Type\IntType::DEFAULT_INT_MIN, -1);
        yield new Builder\IntRangeTypeBuilder('non-positive-int', Type\IntType::DEFAULT_INT_MIN, 0);

        // Adds support for the "float" type
        yield new Builder\SimpleTypeBuilder('float', Type\FloatType::class);

        // Adds support for the "list<T>" type
        yield new Builder\ListTypeBuilder('list');

        // Adds support for the "non-empty<T>" type
        yield new Builder\NonEmptyTypeBuilder('non-empty');

        // Adds support for the "array-key" type
        yield new Builder\SimpleTypeBuilder('array-key', Type\ArrayKeyType::class);

        // Adds support for the "array" type
        yield new Builder\ArrayTypeBuilder('array');

        // Adds support for the "?T" statement
        yield new Builder\NullableTypeBuilder();

        // Adds support for the "null" literal and/or named type statement
        yield new Builder\NullTypeBuilder();

        // Adds support for the "true" and "false" literals
        yield new Builder\BoolLiteralTypeBuilder();

        // Adds support for the integer literal types
        yield new Builder\IntLiteralTypeBuilder();

        // Adds support for the float literal types
        yield new Builder\FloatLiteralTypeBuilder();

        // Adds support for the "T[]" statement
        yield new Builder\TypesListBuilder();

        // Adds support for the "T|U" union types
        yield new Builder\UnionTypeBuilder();

        // Adds support for the "DateTime" and "DateTimeImmutable" types
        yield new Builder\DateTimeTypeBuilder();

        // Adds support for the "BackedEnum" type
        yield new Builder\BackedEnumTypeBuilder();

        // Adds support for the "Path\To\Class" statement
        yield new Builder\ObjectTypeBuilder($this->driver);
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
