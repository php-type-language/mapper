<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Coercer\CoercerInterface;
use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Context\ClassTypes;
use TypeLang\Mapper\Context\Coercers;
use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\PublicViolationFormatter;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Mapper\Diagnostics\ViolationFormatterInterface;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\InvalidValueException\DeclaredTypeFinder;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Hook\ClassHookInterface;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
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
use TypeLang\Mapper\Type\Expander\TypeExpander;
use TypeLang\Mapper\Type\Expander\TypeExpanderInterface;
use TypeLang\Mapper\Type\FloatType;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\RefinedType;
use TypeLang\Mapper\Type\Repository\InspectableTypeRepositoryInterface;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;
use TypeLang\Parser\Exception\ParserExceptionInterface;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Printer\PrinterOptions;
use TypeLang\Printer\TypePrinterInterface;
use TypeLang\Type\TypeNode;

/**
 * Facade: parses the type string once, builds the type once and converts
 * values by it in both directions.
 */
final class Mapper implements MapperInterface
{
    private readonly InspectableTypeRepositoryInterface $repository;

    private readonly Context $context;

    /**
     * Gives the violations of the errors their public expected types
     */
    private readonly TypeExpander $expectedTypes;

    /**
     * Writes the messages of the errors of denormalization
     */
    private readonly ViolationFormatterInterface $denormalizationFormatter;

    /**
     * Writes the messages of the errors of normalization
     */
    private readonly ViolationFormatterInterface $normalizationFormatter;

    /**
     * @var array<non-empty-string, TypeInterface<mixed>>
     */
    private array $types = [];

    /**
     * Parsed types by their strings, the declared types of the violations
     *
     * @var array<non-empty-string, TypeNode>
     */
    private array $nodes = [];

    /**
     * @param list<TypeBuilderInterface<mixed>> $builders builders of the user,
     *        tried before the default ones
     * @param list<ClassHookInterface> $hooks run around the conversion of the
     *        objects of the classes they support, in the order of the list
     * @param list<TypeDecoratorInterface> $decorators wrap the built types in
     *        the order of the list, such as all enums
     * @param list<TypeExpanderInterface> $expanders expand the expected types
     *        of the errors, tried before the default ones
     * @param list<AccessStrategyInterface> $accessors
     * @param list<InstantiationStrategyInterface> $instantiators
     * @param array{
     *    int?: ?CoercerInterface<int>,
     *    float?: ?CoercerInterface<float>,
     *    string?: ?CoercerInterface<string>,
     *    bool?: ?CoercerInterface<bool>,
     *    array?: ?CoercerInterface<array<array-key, mixed>>,
     * } $coercers how values written in another form become values of the types
     * @param MetadataProviderInterface|null $metadata metadata of the classes,
     *        {@see MetadataBuilder::createDefault()} in case of
     *        {@see null}
     * @param ViolationFormatterInterface|null $denormalizationFormatter writes
     *        the messages of the errors of denormalization, of the data of
     *        a client: {@see PublicViolationFormatter} in case of
     *        {@see null}
     * @param ViolationFormatterInterface|null $normalizationFormatter writes
     *        the messages of the errors of normalization, of the values of
     *        the application: {@see ViolationFormatter} in case of
     *        {@see null}
     * @param TypePrinterInterface|null $printer prints the type in errors of
     *        its definition and of values, a single line with all fields of
     *        shapes in case of {@see null}
     * @throws InvalidPatternException in case of a pattern of a default type
     *         is written wrong
     */
    public function __construct(
        Configuration $config = new Configuration(),
        array $builders = [],
        array $hooks = [],
        array $decorators = [],
        array $expanders = [],
        array $accessors = [],
        array $instantiators = [],
        array $coercers = [],
        ?MetadataProviderInterface $metadata = null,
        ?ViolationFormatterInterface $denormalizationFormatter = null,
        ?ViolationFormatterInterface $normalizationFormatter = null,
        ?TypePrinterInterface $printer = null,
        private readonly TypeParser $parser = new TypeParser(),
    ) {
        $classes = new ClassAccessors(
            properties: PropertyAccessorFactory::createDefault($accessors),
            instantiators: InstantiatorFactory::createDefault($instantiators),
            hooks: $hooks,
        );

        $printer ??= self::createDefaultTypePrinter();

        $this->repository = new TypeRepository(
            builders: self::createDefaultTypes($this->parser, $builders),
            metadata: $metadata ?? MetadataBuilder::createDefault()
                ->build(),
            decorators: $decorators,
            accessors: $classes,
            printer: $printer,
        );

        $this->denormalizationFormatter = $denormalizationFormatter
            ?? self::createDefaultDenormalizationFormatter($printer);
        $this->normalizationFormatter = $normalizationFormatter
            ?? self::createDefaultNormalizationFormatter($printer);

        $this->expectedTypes = TypeExpander::createDefault(
            types: $this->repository,
            expanders: $expanders,
            maxDepth: $config->expectedTypeDepth,
            maxFields: $config->expectedTypeMaxFields,
        );

        $this->context = new Context(
            types: new ClassTypes($this->repository),
            config: $config,
            accessors: $classes,
            coercers: Coercers::createDefault(...$coercers),
            declaredTypes: new DeclaredTypeFinder($this->repository),
        );
    }

    /**
     * Creates the printer of the types: a single line with all fields of
     * shapes.
     */
    private static function createDefaultTypePrinter(): TypePrinterInterface
    {
        return new PrettyTypePrinter(
            new PrinterOptions(maxInlineShapeFields: \PHP_INT_MAX),
        );
    }

    /**
     * Creates the formatter of the errors of the data of a client: without
     * the classes, enums and constants of the application.
     */
    private static function createDefaultDenormalizationFormatter(
        TypePrinterInterface $printer,
    ): ViolationFormatterInterface {
        return new PublicViolationFormatter($printer);
    }

    /**
     * Creates the formatter of the errors of the values of the application,
     * for a developer: with the declared types.
     */
    private static function createDefaultNormalizationFormatter(
        TypePrinterInterface $printer,
    ): ViolationFormatterInterface {
        return new ViolationFormatter(new ShortValuePrinter(), $printer, shouldPrintDeclaredType: true);
    }

    /**
     * @param list<TypeBuilderInterface<mixed>> $builders
     * @return list<TypeBuilderInterface<mixed>>
     * @throws InvalidPatternException in case of a pattern is written wrong
     */
    private static function createDefaultTypes(TypeParserInterface $parser, array $builders = []): array
    {
        $string = new StringType();
        $int = new IntType();
        $float = new FloatType();
        $nonEmpty = new NonEmptyCheck();
        $bool = new BoolType();
        $numericString = new RefinedType($string, [new NumericCheck()]);
        $scalar = new UnionType([$int, $float, $bool, $string]);
        $mixed = new MixedType();

        return [
            ...$builders,
            new NamedTypeBuilder(new NamedTypePattern(['int', 'integer', 'literal-int'], $parser), $int),
            new NamedTypeBuilder(new NamedTypePattern(['scalar'], $parser), $scalar),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-scalar'], $parser),
                new RefinedType($scalar, [new NonFalsyCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['empty-scalar'], $parser),
                new RefinedType($scalar, [new FalsyCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['decimal-int-string'], $parser),
                new RefinedType($string, [new DecimalIntCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-decimal-int-string'], $parser),
                new RefinedType($string, [new DecimalIntCheck(false)]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['positive-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(4.9E-324, \INF, '> 0')]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['negative-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(-\INF, -4.9E-324, '< 0')]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-positive-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(-\INF, 0.0, '<= 0')]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-negative-float'], $parser),
                new RefinedType($float, [new FloatRangeCheck(0.0, \INF, '>= 0')]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['finite-float'], $parser),
                new RefinedType(
                    $float,
                    [new FloatRangeCheck(-\PHP_FLOAT_MAX, \PHP_FLOAT_MAX, 'finite')],
                ),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-mixed'], $parser),
                new RefinedType($mixed, [new NonFalsyCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-null-mixed'], $parser),
                new RefinedType($mixed, [new NonNullCheck()]),
            ),
            new NamedTypeBuilder(new NamedTypePattern(['float', 'double', 'real'], $parser), $float),
            new NamedTypeBuilder(
                new NamedTypePattern(['number'], $parser),
                new UnionType([$int, $float]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['numeric'], $parser),
                new UnionType([$int, $float, $numericString]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-zero-int'], $parser),
                new RefinedType($int, [new NonFalsyCheck()]),
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
                new RefinedType($string, [$nonEmpty]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['truthy-string', 'non-falsy-string'], $parser),
                new RefinedType($string, [new NonFalsyCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['lowercase-string'], $parser),
                new RefinedType($string, [new LowercaseCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-lowercase-string'], $parser),
                new RefinedType($string, [$nonEmpty, new LowercaseCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['uppercase-string'], $parser),
                new RefinedType($string, [new UppercaseCheck()]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-uppercase-string'], $parser),
                new RefinedType($string, [$nonEmpty, new UppercaseCheck()]),
            ),
            new NamedTypeBuilder(new NamedTypePattern(['bool', 'boolean'], $parser), $bool),
            new NamedTypeBuilder(new NamedTypePattern(['mixed'], $parser), $mixed),
            new NamedTypeBuilder(new NamedTypePattern(['string'], $parser), $string),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-empty-string'], $parser),
                new RefinedType($string, [$nonEmpty]),
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
                new RefinedType($int, [new IntRangeCheck(min: 1)]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['negative-int'], $parser),
                new RefinedType($int, [new IntRangeCheck(max: -1)]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-positive-int'], $parser),
                new RefinedType($int, [new IntRangeCheck(max: 0)]),
            ),
            new NamedTypeBuilder(
                new NamedTypePattern(['non-negative-int'], $parser),
                new RefinedType($int, [new IntRangeCheck(min: 0)]),
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
        $built = $this->getType($type);

        try {
            return $built->denormalize($value, $this->context);
        } catch (InvalidValueException $e) {
            throw $e
                ->withDeclaredType($this->nodes[$type], $this->context->declaredTypes)
                ->withExpectedTypes($this->expectedTypes, $this->denormalizationFormatter);
        }
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
        $type ??= 'mixed';
        $built = $this->getType($type);

        try {
            return $built->normalize($value, $this->context);
        } catch (InvalidValueException $e) {
            throw $e
                ->withDeclaredType($this->nodes[$type], $this->context->declaredTypes)
                ->withPublicMessage($this->normalizationFormatter);
        }
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

        $built = $this->repository->getType($node);
        $this->nodes[$type] = $node;

        return $this->types[$type] = $built;
    }
}
