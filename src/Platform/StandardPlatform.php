<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Runtime\Context\Direction;
use TypeLang\Mapper\Type;
use TypeLang\Mapper\Type\Builder;
use TypeLang\Mapper\Type\Builder\ClassFromArrayTypeBuilder;

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

    public function getTypes(Direction $direction): iterable
    {
        yield from parent::getTypes($direction);

        // Adds support for the "mixed" type
        yield new Builder\SimpleTypeBuilder('mixed', Type\MixedType::class);

        // Adds support for the "bool" type
        yield new Builder\SimpleTypeBuilder(['bool', 'boolean'], Type\BoolType::class);

        // Adds support for the "string" type
        yield new Builder\SimpleTypeBuilder(['string', \Stringable::class], Type\StringType::class);

        // Adds support for the "int" type
        yield new Builder\IntRangeTypeBuilder(['int', 'integer']);

        // Adds support for the "float" type
        yield new Builder\SimpleTypeBuilder(['float', 'double', 'real'], Type\FloatType::class);

        // Adds support for the "array-key" type
        yield new Builder\SimpleTypeBuilder('array-key', Type\ArrayKeyType::class);

        // Adds support for the "array" type
        yield new Builder\ArrayTypeBuilder([
            'array',
            'iterable',
            \Iterator::class,
            \Generator::class,
            \Traversable::class,
            \IteratorAggregate::class,
        ], 'array-key', 'mixed');

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

        if ($direction === Direction::Normalize) {
            // Adds support for the "iterable<T> -> list<T>" type
            yield new Builder\ListFromIterableTypeBuilder('list', 'mixed');
            // Adds support for the "object -> array{ ... }" type
            yield new Builder\ObjectToArrayTypeBuilder(['object', \stdClass::class]);
            // Adds support for the "BackedEnum -> scalar" type
            yield new Builder\BackedEnumToScalarTypeBuilder();
            // Adds support for the "UnitEnum -> scalar" type
            yield new Builder\UnitEnumToScalarTypeBuilder();
            // Adds support for the "DateTimeInterface -> string" type
            yield new Builder\DateTimeToStringTypeBuilder();
            // Adds support for the "object(ClassName) -> array{ ... }" type
            yield new Builder\ClassToArrayTypeBuilder($this->meta);
        } else {
            // Adds support for the "array<T> -> list<T>" type
            yield new Builder\ListFromArrayTypeBuilder('list', 'mixed');
            // Adds support for the "array{ ... } -> object" type
            yield new Builder\ObjectFromArrayTypeBuilder(['object', \stdClass::class]);
            // Adds support for the "scalar -> BackedEnum" type
            yield new Builder\BackedEnumFromScalarTypeBuilder();
            // Adds support for the "scalar -> UnitEnum" type
            yield new Builder\UnitEnumFromScalarTypeBuilder();
            // Adds support for the "string -> DateTime|DateTimeImmutable" type
            yield new Builder\DateTimeFromStringTypeBuilder();
            // Adds support for the "array{ ... } -> object(ClassName)" type
            yield new ClassFromArrayTypeBuilder($this->meta);
        }
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
