<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Context\ClassTypes;
use TypeLang\Mapper\Context\Coercers;
use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Hook\ClassHookInterface;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Mapping\MetadataProviderFactory;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\Type\ArrayKeyType;
use TypeLang\Mapper\Type\BoolType;
use TypeLang\Mapper\Type\Builder\ArrayTypeBuilder;
use TypeLang\Mapper\Type\Builder\BackedEnumTypeBuilder;
use TypeLang\Mapper\Type\Builder\ClassTypeBuilder;
use TypeLang\Mapper\Type\Builder\ConstantTypeBuilder;
use TypeLang\Mapper\Type\Builder\DateTimeTypeBuilder;
use TypeLang\Mapper\Type\Builder\DiscriminatorTypeBuilder;
use TypeLang\Mapper\Type\Builder\IntMaskTypeBuilder;
use TypeLang\Mapper\Type\Builder\IntRangeTypeBuilder;
use TypeLang\Mapper\Type\Builder\IterableTypeBuilder;
use TypeLang\Mapper\Type\Builder\ListTypeBuilder;
use TypeLang\Mapper\Type\Builder\LiteralTypeBuilder;
use TypeLang\Mapper\Type\Builder\NamedTypeBuilder;
use TypeLang\Mapper\Type\Builder\NullTypeBuilder;
use TypeLang\Mapper\Type\Builder\ObjectTypeBuilder;
use TypeLang\Mapper\Type\Builder\ShapeTypeBuilder;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Builder\TypesListTypeBuilder;
use TypeLang\Mapper\Type\Builder\UnionTypeBuilder;
use TypeLang\Mapper\Type\Builder\UnitEnumTypeBuilder;
use TypeLang\Mapper\Type\Builder\ValueOfTypeBuilder;
use TypeLang\Mapper\Type\Check\DecimalIntCheck;
use TypeLang\Mapper\Type\Check\FalsyCheck;
use TypeLang\Mapper\Type\Check\FloatRangeCheck;
use TypeLang\Mapper\Type\Check\IntRangeCheck;
use TypeLang\Mapper\Type\Check\LowercaseCheck;
use TypeLang\Mapper\Type\Check\NonEmptyCheck;
use TypeLang\Mapper\Type\Check\NonFalsyCheck;
use TypeLang\Mapper\Type\Check\NonNullCheck;
use TypeLang\Mapper\Type\Check\NumericCheck;
use TypeLang\Mapper\Type\Check\UppercaseCheck;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\FloatType;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\RefinedType;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Parser\Exception\ParserExceptionInterface;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Facade: parses the type string once, builds the type once and converts
 * values by it in both directions.
 */
final class Mapper implements MapperInterface
{
    private readonly TypeRepositoryInterface $repository;

    private readonly Context $context;

    /**
     * @var array<non-empty-string, TypeInterface<mixed>>
     */
    private array $types = [];

    /**
     * @param list<TypeBuilderInterface<mixed>> $builders builders of the
     *        user, tried before the default ones
     * @param list<TypeDecoratorInterface> $decorators wrap the built types
     *        in the order of the list, such as all enums
     * @param list<ClassHookInterface> $hooks run around the conversion of
     *        the objects of the classes they support, in the order of the
     *        list
     * @param Coercers $coercers how values written in another form become
     *        values of the types
     * @throws InvalidPatternException in case of a pattern of a default type
     *         is written wrong
     */
    public function __construct(
        Configuration $config = new Configuration(),
        ?MetadataProviderInterface $metadata = null,
        array $builders = [],
        array $decorators = [],
        private readonly TypeParser $parser = new TypeParser(),
        PropertyAccessorFactory $accessors = new PropertyAccessorFactory(),
        InstantiatorFactory $instantiators = new InstantiatorFactory(),
        array $hooks = [],
        Coercers $coercers = new Coercers(),
    ) {
        $classes = new ClassAccessors($accessors, $instantiators, $hooks);

        $this->repository = new TypeRepository(
            builders: [...$builders, ...self::createDefaultBuilders($this->parser)],
            metadata: $metadata ?? MetadataProviderFactory::create(),
            decorators: $decorators,
            accessors: $classes,
        );

        $this->context = new Context(new ClassTypes($this->repository), $config, $classes, $coercers);
    }

    /**
     * @return list<TypeBuilderInterface<mixed>>
     * @throws InvalidPatternException in case of a pattern is written wrong
     */
    public static function createDefaultBuilders(TypeParserInterface $parser = new TypeParser()): array
    {
        $string = new StringType();
        $int = new IntType();
        $float = new FloatType();
        $nonEmpty = new NonEmptyCheck();
        $bool = new BoolType();
        $numericString = new RefinedType($string, [new NumericCheck()], 'numeric-string');
        $scalar = new UnionType([$int, $float, $bool, $string], 'scalar');
        $mixed = new MixedType();

        return [
            new NamedTypeBuilder(new NamedTypePattern(['int', 'integer', 'literal-int'], $parser), $int),
            new NamedTypeBuilder(new NamedTypePattern(['scalar'], $parser), $scalar),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-scalar'], $parser),
                new RefinedType($scalar, [new NonFalsyCheck()], 'non-empty-scalar'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['empty-scalar'], $parser),
                new RefinedType($scalar, [new FalsyCheck()], 'empty-scalar'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['decimal-int-string'], $parser),
                new RefinedType($string, [new DecimalIntCheck()], 'decimal-int-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-decimal-int-string'], $parser),
                new RefinedType($string, [new DecimalIntCheck(false)], 'non-decimal-int-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['positive-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(4.9E-324, \INF, '> 0')], 'positive-float'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['negative-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(-\INF, -4.9E-324, '< 0')], 'negative-float'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-positive-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(-\INF, 0.0, '<= 0')], 'non-positive-float'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-negative-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(0.0, \INF, '>= 0')], 'non-negative-float'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['finite-float'], $parser),
                new RefinedType(
                    $float,
                    [new FloatRangeCheck(-\PHP_FLOAT_MAX, \PHP_FLOAT_MAX, 'finite')],
                    'finite-float',
                ),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-mixed'], $parser),
                new RefinedType($mixed, [new NonFalsyCheck()], 'non-empty-mixed'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-null-mixed'], $parser),
                new RefinedType($mixed, [new NonNullCheck()], 'non-null-mixed'),
            ),
            new NamedTypeBuilder(new NamedTypePattern(['float', 'double', 'real'], $parser), $float),
            new NamedTypeBuilder(
                new NamedTypePattern(['number'], $parser),
                new UnionType([$int, $float], 'number'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['numeric'], $parser),
                new UnionType([$int, $float, $numericString], 'numeric'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-zero-int'], $parser),
                new RefinedType($int, [new NonFalsyCheck()], 'non-zero-int'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(
                    [
                        'literal-string',
                        'callable-string',
                        'class-string',
                        'class-string<T>',
                        'interface-string',
                        'interface-string<T>',
                        'trait-string',
                        'enum-string',
                        'enum-string<T>',
                    ],
                    $parser,
                ),
                $string,
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-literal-string'], $parser),
                new RefinedType($string, [$nonEmpty], 'non-empty-literal-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['truthy-string', 'non-falsy-string'], $parser),
                new RefinedType($string, [new NonFalsyCheck()], 'non-falsy-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['lowercase-string'], $parser),
                new RefinedType($string, [new LowercaseCheck()], 'lowercase-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-lowercase-string'], $parser),
                new RefinedType($string, [$nonEmpty, new LowercaseCheck()], 'non-empty-lowercase-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['uppercase-string'], $parser),
                new RefinedType($string, [new UppercaseCheck()], 'uppercase-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-uppercase-string'], $parser),
                new RefinedType($string, [$nonEmpty, new UppercaseCheck()], 'non-empty-uppercase-string'),
            ),
            new NamedTypeBuilder(new NamedTypePattern(['bool', 'boolean'], $parser), $bool),
            new NamedTypeBuilder(new NamedTypePattern(['mixed'], $parser), $mixed),
            new NamedTypeBuilder(new NamedTypePattern(['string'], $parser), $string),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-string'], $parser),
                new RefinedType($string, [$nonEmpty], 'non-empty-string'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['numeric-string'], $parser),
                $numericString,
            ),
            new IntRangeTypeBuilder(new NamedTypePattern(['int<min, max>'], $parser), $int),
            new IntMaskTypeBuilder($int),
            new ValueOfTypeBuilder(),
            new NamedTypeBuilder(
                new NamedTypePattern(['positive-int'], $parser),
                new RefinedType($int, [new IntRangeCheck(min: 1)], 'positive-int'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['negative-int'], $parser),
                new RefinedType($int, [new IntRangeCheck(max: -1)], 'negative-int'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-positive-int'], $parser),
                new RefinedType($int, [new IntRangeCheck(max: 0)], 'non-positive-int'),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-negative-int'], $parser),
                new RefinedType($int, [new IntRangeCheck(min: 0)], 'non-negative-int'),
            ),
            new NamedTypeBuilder(new NamedTypePattern(['array-key'], $parser), new ArrayKeyType()),
            new ListTypeBuilder(new NamedTypePattern(['list', 'list<T>'], $parser)),
            new ListTypeBuilder(
                new NamedTypePattern(['non-empty-list', 'non-empty-list<T>'], $parser),
                [new NonEmptyCheck()],
            ),
            new ArrayTypeBuilder(new NamedTypePattern(
                [
                    'array',
                    'array<V>',
                    'array<K, V>',
                    'associative-array',
                    'associative-array<V>',
                    'associative-array<K, V>',
                ],
                $parser,
            )),
            new TypesListTypeBuilder(),
            new ArrayTypeBuilder(
                new NamedTypePattern(
                    [
                        'non-empty-array',
                        'non-empty-array<V>',
                        'non-empty-array<K, V>',
                        'non-empty-associative-array',
                        'non-empty-associative-array<V>',
                        'non-empty-associative-array<K, V>',
                    ],
                    $parser,
                ),
                [new NonEmptyCheck()],
            ),
            new IterableTypeBuilder(new NamedTypePattern(['iterable', 'iterable<V>', 'iterable<K, V>'], $parser)),
            new ShapeTypeBuilder(),
            new NullTypeBuilder(),
            new LiteralTypeBuilder(),
            new ConstantTypeBuilder(),
            new UnionTypeBuilder(),
            new BackedEnumTypeBuilder(),
            new UnitEnumTypeBuilder(),
            new ObjectTypeBuilder(),
            new DateTimeTypeBuilder(),
            new DiscriminatorTypeBuilder(),
            new ClassTypeBuilder(),
        ];
    }

    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed
    {
        return $this->getType($type)->denormalize($value, $this->context);
    }

    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool
    {
        return $this->getType($type)->isDenormalizable($value, $this->context);
    }

    /**
     * A value without the type is normalized by `mixed`: an object by the
     * type of its class, an array element by element.
     */
    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        return $this->getType($type ?? 'mixed')->normalize($value, $this->context);
    }

    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        return $this->getType($type ?? 'mixed')->isNormalizable($value, $this->context);
    }

    /**
     * @param non-empty-string $type
     * @return TypeInterface<mixed>
     * @throws TypeDefinitionException in case of the type is written wrong
     *         or cannot be built
     */
    private function getType(string $type): TypeInterface
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        try {
            /** @var TypeNode */
            $node = $this->parser->forType()->parse($type);
        } catch (ParserExceptionInterface $e) {
            throw TypeDefinitionException::becauseTypeIsWrittenWrong($type, $e);
        }

        return $this->types[$type] = $this->repository->getType($node);
    }
}
