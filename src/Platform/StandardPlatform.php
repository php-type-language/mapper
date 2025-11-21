<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Coercer;
use TypeLang\Mapper\Type;
use TypeLang\Mapper\Type\Builder;
use TypeLang\Mapper\Type\Builder\TypeAliasBuilder\Reason;

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
    public function getTypes(): iterable
    {
        yield from parent::getTypes();

        // Adds support for the "mixed" type
        yield new Builder\SimpleTypeBuilder('mixed', Type\MixedType::class);

        // Adds support for the "bool" type
        yield $bool = new Builder\SimpleTypeBuilder('bool', Type\BoolType::class);
        yield new Builder\TypeAliasBuilder('boolean', $bool, Reason::NonCanonical);

        // Adds support for the "string" type
        yield $string = new Builder\SimpleTypeBuilder('string', Type\StringType::class);
        yield new Builder\TypeAliasBuilder(\Stringable::class, $string);
        yield new Builder\TypeAliasBuilder('non-empty-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('lowercase-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('non-empty-lowercase-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('uppercase-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('non-empty-uppercase-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('numeric-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('literal-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('non-empty-literal-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('class-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('interface-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('trait-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('enum-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('callable-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('truthy-string', $string, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('non-falsy-string', $string, Reason::Temporary);

        // Adds support for the "int" type
        yield $int = new Builder\IntRangeTypeBuilder('int');
        yield new Builder\TypeAliasBuilder('integer', $int, Reason::NonCanonical);
        yield new Builder\TypeAliasBuilder('positive-int', $int, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('non-positive-int', $int, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('negative-int', $int, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('non-negative-int', $int, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('non-zero-int', $int, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('number', $int, Reason::Temporary);
        yield new Builder\TypeAliasBuilder('numeric', $int, Reason::Temporary);

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

        // Adds support for the literal types
        yield new Builder\NullTypeBuilder();
        yield new Builder\BoolLiteralTypeBuilder();
        yield new Builder\IntLiteralTypeBuilder();
        yield new Builder\FloatLiteralTypeBuilder();
        yield new Builder\StringLiteralTypeBuilder();

        // Adds support for the "T[]" statement
        yield new Builder\TypesListBuilder();

        // Adds support for the "T|U" union types
        yield new Builder\UnionTypeBuilder();

        // Adds support for constants (and masks)
        yield new Builder\ConstMaskTypeBuilder();
        yield new Builder\ClassConstTypeBuilder();
        yield new Builder\ClassConstMaskTypeBuilder();

        // Other (asymmetric)
        yield $object = new Builder\ObjectTypeBuilder('object');
        yield new Builder\TypeAliasBuilder(\stdClass::class, $object);
        yield new Builder\UnitEnumTypeBuilder();
        yield new Builder\BackedEnumTypeBuilder();
        yield new Builder\DateTimeTypeBuilder();
        yield new Builder\ClassTypeBuilder(
            meta: $this->getMetadataProvider(),
            accessor: $this->getPropertyAccessor(),
            instantiator: $this->getClassInstantiator(),
        );
    }

    #[\Override]
    public function getTypeCoercers(): iterable
    {
        yield from parent::getTypeCoercers();

        yield new Coercer\ArrayKeyTypeCoercer() => [
            Type\ArrayKeyType::class,
        ];

        yield new Coercer\BoolTypeCoercer() => [
            Type\BoolType::class,
        ];

        yield new Coercer\FloatTypeCoercer() => [
            Type\FloatType::class,
        ];

        yield new Coercer\IntTypeCoercer() => [
            Type\IntType::class,
            Type\IntRangeType::class,
        ];

        yield new Coercer\StringTypeCoercer() => [
            Type\StringType::class,
        ];

        yield new Coercer\ArrayTypeCoercer() => [
            Type\ArrayType::class,
        ];

        yield new Coercer\ListTypeCoercer() => [
            Type\ListType::class,
        ];
    }

    #[\Override]
    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
