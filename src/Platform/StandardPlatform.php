<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Type;
use TypeLang\Mapper\Type\Builder;
use TypeLang\Mapper\Type\Coercer;
use TypeLang\Mapper\Type\Specifier;

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
    ];

    public function getName(): string
    {
        return 'standard';
    }

    public function getTypes(Direction $direction): iterable
    {
        $intCoercer = new Coercer\IntTypeCoercer();
        $boolCoercer = new Coercer\BoolTypeCoercer();
        $floatCoercer = new Coercer\FloatTypeCoercer();
        $stringCoercer = new Coercer\StringTypeCoercer();
        $arrayKeyCoercer = new Coercer\ArrayKeyTypeCoercer();

        yield from parent::getTypes($direction);

        //
        // Adds support for the "mixed" type
        //

        yield new Builder\MixedTypeBuilder('mixed');

        //
        // Adds support for the "bool" type
        //

        yield new Builder\Literal\BoolLiteralTypeBuilder();
        yield $bool = new Builder\ScalarTypeBuilder(
            name: 'bool',
            class: Type\BoolType::class,
            coercer: $boolCoercer,
        );

        yield new Builder\TypeAliasBuilder(
            aliases: 'boolean',
            delegate: $bool,
        );

        //
        // Adds support for the "string" type
        //

        yield new Builder\Literal\StringLiteralTypeBuilder();
        yield new Builder\ScalarTypeBuilder(
            name: ['string', \Stringable::class],
            class: Type\StringType::class,
            coercer: $stringCoercer,
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'non-empty-string',
            class: Type\StringType::class,
            coercer: $stringCoercer,
            specifier: new Specifier\NonEmptyStringSpecifier(),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'lowercase-string',
            class: Type\StringType::class,
            coercer: $stringCoercer,
            specifier: new Specifier\LowercaseStringSpecifier(),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'non-empty-lowercase-string',
            class: Type\StringType::class,
            coercer: $stringCoercer,
            specifier: new Specifier\AllOfSpecifier([
                new Specifier\LowercaseStringSpecifier(),
                new Specifier\NonEmptyStringSpecifier(),
            ]),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'uppercase-string',
            class: Type\StringType::class,
            coercer: $stringCoercer,
            specifier: new Specifier\UppercaseStringSpecifier(),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'non-empty-uppercase-string',
            class: Type\StringType::class,
            coercer: $stringCoercer,
            specifier: new Specifier\AllOfSpecifier([
                new Specifier\UppercaseStringSpecifier(),
                new Specifier\NonEmptyStringSpecifier(),
            ]),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'numeric-string',
            class: Type\StringType::class,
            coercer: $stringCoercer,
            specifier: new Specifier\NumericStringSpecifier(),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'non-empty-numeric-string',
            class: Type\StringType::class,
            coercer: $stringCoercer,
            specifier: new Specifier\AllOfSpecifier([
                new Specifier\NonEmptyStringSpecifier(),
                new Specifier\NumericStringSpecifier(),
            ])
        );


        //
        // Adds support for the "int" type
        //

        yield new Builder\Literal\IntLiteralTypeBuilder();
        yield $int = new Builder\IntRangeTypeBuilder(
            name: 'int',
            coercer: $intCoercer,
        );

        yield new Builder\TypeAliasBuilder(
            aliases: 'integer',
            delegate: $int,
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'positive-int',
            class: Type\IntType::class,
            coercer: $intCoercer,
            specifier: new Specifier\IntGreaterThanOrEqualSpecifier(1),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'non-positive-int',
            class: Type\IntType::class,
            coercer: $intCoercer,
            specifier: new Specifier\IntLessThanOrEqualSpecifier(0),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'negative-int',
            class: Type\IntType::class,
            coercer: $intCoercer,
            specifier: new Specifier\IntLessThanOrEqualSpecifier(-1),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'non-negative-int',
            class: Type\IntType::class,
            coercer: $intCoercer,
            specifier: new Specifier\IntGreaterThanOrEqualSpecifier(0),
        );

        yield new Builder\ScalarTypeBuilder(
            name: 'non-zero-int',
            class: Type\IntType::class,
            coercer: $intCoercer,
            specifier: new Specifier\NotSpecifier(
                delegate: new Specifier\SameSpecifier(0),
            ),
        );


        //
        // Adds support for the "float" type
        //

        yield new Builder\Literal\FloatLiteralTypeBuilder();

        yield $float = new Builder\ScalarTypeBuilder(
            name: 'float',
            class: Type\FloatType::class,
            coercer: $floatCoercer,
        );

        yield new Builder\TypeAliasBuilder(
            aliases: ['double', 'real'],
            delegate: $float,
        );

        //
        // Other types
        //

        // Adds support for the "array-key" type
        yield new Builder\ScalarTypeBuilder(
            name: 'array-key',
            class: Type\ArrayKeyType::class,
            coercer: $arrayKeyCoercer,
        );

        // Adds support for the "array" type
        yield new Builder\ArrayTypeBuilder(
            name: [
                'array',
                'iterable',
                \Iterator::class,
                \Generator::class,
                \Traversable::class,
                \IteratorAggregate::class,
            ],
            keyType: 'array-key',
            valueType: 'mixed',
        );

        // Adds support for the "null" and "?T" statement
        yield new Builder\NullableTypeBuilder();
        yield new Builder\NullTypeBuilder();

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
            yield new Builder\ClassFromArrayTypeBuilder($this->meta);
        }
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
