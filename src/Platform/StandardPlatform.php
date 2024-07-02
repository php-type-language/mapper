<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

use TypeLang\Mapper\Meta\Reader\AttributeReader;
use TypeLang\Mapper\Meta\Reader\ReaderInterface;
use TypeLang\Mapper\PlatformInterface;
use TypeLang\Mapper\Type;
use TypeLang\Mapper\Type\Builder\ListTypeBuilder;
use TypeLang\Mapper\Type\Builder\NamedTypeBuilder;
use TypeLang\Mapper\Type\Builder\NullableTypeBuilder;
use TypeLang\Mapper\Type\Builder\ObjectNamedTypeBuilder;
use TypeLang\Mapper\Type\Builder\ObjectTypeBuilder;
use TypeLang\Mapper\Type\Builder\UnionTypeBuilder;

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
    ];

    public function __construct(
        private readonly ReaderInterface $reader = new AttributeReader(),
    ) {}

    public function getName(): string
    {
        return 'standard';
    }

    public function getBuiltinTypes(): iterable
    {
        yield new NamedTypeBuilder('null', Type\NullType::class);
        yield new NamedTypeBuilder('mixed', Type\MixedType::class);
        yield new NamedTypeBuilder('int', Type\IntType::class);
        yield new NamedTypeBuilder('bool', Type\BoolType::class);
        yield new NamedTypeBuilder('string', Type\StringType::class);
        yield new NamedTypeBuilder('float', Type\FloatType::class);
        // @phpstan-ignore-next-line : Allow non-resolvable types
        yield new NamedTypeBuilder('list', Type\ListType::class);
        // @phpstan-ignore-next-line : Allow non-resolvable types
        yield new NamedTypeBuilder('array', Type\ArrayType::class);
        yield new ObjectNamedTypeBuilder(\DateTimeInterface::class, Type\DateTimeType::class);
        yield new ObjectNamedTypeBuilder(\BackedEnum::class, Type\BackedEnumType::class);
        yield new NullableTypeBuilder();
        yield new ListTypeBuilder();
        yield new UnionTypeBuilder();
        yield new ObjectTypeBuilder($this->reader);
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
