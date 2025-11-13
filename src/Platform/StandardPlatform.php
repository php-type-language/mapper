<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Type;
use TypeLang\Mapper\Type\Builder;
use TypeLang\Mapper\Type\Builder\TypeAliasBuilder\Reason;
use TypeLang\Mapper\Type\Coercer;

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

    #[\Override]
    public function getTypes(DirectionInterface $direction): iterable
    {
        yield from parent::getTypes($direction);

        // Adds support for the "mixed" type
        yield new Builder\SimpleTypeBuilder('mixed', Type\MixedType::class);

        // Adds support for the "bool" type
        yield $bool = new Builder\SimpleTypeBuilder('bool', Type\BoolType::class);
        yield new Builder\TypeAliasBuilder('boolean', $bool, Reason::NonCanonical);

        // Adds support for the "string" type
        yield $string = new Builder\SimpleTypeBuilder('string', Type\StringType::class);
        yield new Builder\TypeAliasBuilder(\Stringable::class, $string);

        // Adds support for the "int" type
        yield $int = new Builder\IntRangeTypeBuilder('int');
        yield new Builder\TypeAliasBuilder('integer', $int, Reason::NonCanonical);

        // Adds support for the "float" type
        yield $float = new Builder\SimpleTypeBuilder('float', Type\FloatType::class);
        yield new Builder\TypeAliasBuilder('double', $float, Reason::NonCanonical);
        yield new Builder\TypeAliasBuilder('real', $float, Reason::Deprecated);

        // Adds support for the "array-key" type
        yield new Builder\SimpleTypeBuilder('array-key', Type\ArrayKeyType::class);

        // Adds support for the "array" type
        yield $array = new Builder\ArrayTypeBuilder('array', 'array-key', 'mixed');
        // Adds support for the "iterable" type
        yield $iterable = new Builder\IterableToArrayTypeBuilder('iterable', 'array-key', 'mixed');
        yield new Builder\TypeAliasBuilder(\Iterator::class, $iterable);
        yield new Builder\TypeAliasBuilder(\Generator::class, $iterable);
        yield new Builder\TypeAliasBuilder(\Traversable::class, $iterable);
        yield new Builder\TypeAliasBuilder(\IteratorAggregate::class, $iterable);

        // Adds support for the "iterable<T> -> list<T>" type
        yield new Builder\ListTypeBuilder('list', 'mixed');

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

        // Adds support for the string literal types
        yield new Builder\StringLiteralTypeBuilder();

        // Adds support for the "T[]" statement
        yield new Builder\TypesListBuilder();

        // Adds support for the "T|U" union types
        yield new Builder\UnionTypeBuilder();

        // Temporary aliases
        yield new Builder\TypeAliasBuilder('non-empty-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('lowercase-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('non-empty-lowercase-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('uppercase-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('non-empty-uppercase-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('numeric-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('literal-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('non-empty-literal-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('class-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('interface-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('trait-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('enum-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('callable-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('truthy-string', $string, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('non-falsy-string', $string, Reason::Deprecated);

        yield new Builder\TypeAliasBuilder('positive-int', $int, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('non-positive-int', $int, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('negative-int', $int, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('non-negative-int', $int, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('non-zero-int', $int, Reason::Deprecated);

        yield new Builder\TypeAliasBuilder('number', $int, Reason::Deprecated);
        yield new Builder\TypeAliasBuilder('numeric', $int, Reason::Deprecated);

        // Other
        if ($direction->isOutput()) {
            // Adds support for the "object -> array{ ... }" type
            yield $object = new Builder\ObjectToArrayTypeBuilder('object');
            yield new Builder\TypeAliasBuilder(\stdClass::class, $object);
            // Adds support for the "BackedEnum -> scalar" type
            yield new Builder\BackedEnumToScalarTypeBuilder();
            // Adds support for the "UnitEnum -> scalar" type
            yield new Builder\UnitEnumToScalarTypeBuilder();
            // Adds support for the "DateTimeInterface -> string" type
            yield new Builder\DateTimeToStringTypeBuilder();
            // Adds support for the "object(ClassName) -> array{ ... }" type
            yield new Builder\ClassToArrayTypeBuilder($this->meta);
        } else {
            // Adds support for the "array{ ... } -> object" type
            yield $object = new Builder\ObjectFromArrayTypeBuilder('object');
            yield new Builder\TypeAliasBuilder(\stdClass::class, $object);
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

    #[\Override]
    public function getTypeCoercers(DirectionInterface $direction): iterable
    {
        yield from parent::getTypeCoercers($direction);

        // array-key
        yield Type\ArrayKeyType::class => $arrayKey = new Coercer\ArrayKeyTypeCoercer();

        // bool
        yield Type\BoolType::class => $bool = new Coercer\BoolTypeCoercer();
        yield Type\BoolLiteralType::class => $bool;

        // float
        yield Type\FloatType::class => $float = new Coercer\FloatTypeCoercer();
        yield Type\FloatLiteralType::class => $float;

        // int
        yield Type\IntType::class => $int = new Coercer\IntTypeCoercer();
        yield Type\IntRangeType::class => $int;
        yield Type\IntLiteralType::class => $int;

        // string
        yield Type\StringType::class => $string = new Coercer\StringTypeCoercer();
        yield Type\StringLiteralType::class => $string;

        // array
        yield Type\ArrayType::class => $array = new Coercer\ArrayTypeCoercer();

        // list
        yield Type\ListType::class => $list = new Coercer\ListTypeCoercer();
    }

    #[\Override]
    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
