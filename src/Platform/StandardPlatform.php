<?php

declare(strict_types=1);

namespace Serafim\Mapper\Platform;

use Serafim\Mapper\Meta\Reader\AttributeReader;
use Serafim\Mapper\Meta\Reader\ReaderInterface;
use Serafim\Mapper\PlatformInterface;
use Serafim\Mapper\Type;
use Serafim\Mapper\Type\Builder\ListTypeBuilder;
use Serafim\Mapper\Type\Builder\NamedTypeBuilder;
use Serafim\Mapper\Type\Builder\NullableTypeBuilder;
use Serafim\Mapper\Type\Builder\ObjectNamedTypeBuilder;
use Serafim\Mapper\Type\Builder\ObjectTypeBuilder;

class StandardPlatform implements PlatformInterface
{
    /**
     * @var GrammarFeature
     */
    private const FEATURES_LIST = [
        GrammarFeature::Shapes,
        GrammarFeature::Literals,
        GrammarFeature::Generics,
        GrammarFeature::Union,
        GrammarFeature::Intersection,
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
        yield new NamedTypeBuilder('mixed', Type\MixedType::class);
        yield new NamedTypeBuilder('int', Type\IntType::class);
        yield new NamedTypeBuilder('bool', Type\BoolType::class);
        yield new NamedTypeBuilder('string', Type\StringType::class);
        yield new NamedTypeBuilder('float', Type\FloatType::class);
        yield new NamedTypeBuilder('list', Type\ListType::class);
        yield new NamedTypeBuilder('array', Type\ListType::class);
        yield new ObjectNamedTypeBuilder(\DateTime::class, Type\DateTimeType::class);
        yield new ObjectNamedTypeBuilder(\DateTimeImmutable::class, Type\DateTimeType::class);
        yield new ObjectNamedTypeBuilder(\UnitEnum::class, Type\BackedEnumType::class);
        yield new NullableTypeBuilder();
        yield new ListTypeBuilder();
        yield new ObjectTypeBuilder($this->reader);
    }

    public function isFeatureSupported(GrammarFeature $feature): bool
    {
        return \in_array($feature, self::FEATURES_LIST, true);
    }
}
