<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use Psr\Clock\ClockInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\ConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\RawTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;
use TypeLang\Mapper\Mapping\Reference\Reader\NativeReferencesReader;
use TypeLang\Mapper\Mapping\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Mapper\Mapping\Reference\ReferencesResolver;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

final class MetadataBuilder implements ProviderInterface
{
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private array $metadata = [];

    private readonly ReferencesResolver $references;

    public function __construct(
        private readonly ReaderInterface $reader = new ReflectionReader(),
        private ?ExpressionLanguage $expression = null,
        private readonly ?ClockInterface $clock = null,
        ReferencesReaderInterface $references = new NativeReferencesReader(),
    ) {
        $this->references = new ReferencesResolver($references);
    }

    private function now(): ?int
    {
        $now = $this->clock?->now();

        return $now?->getTimestamp();
    }

    /**
     * @template TArg of object
     *
     * @param \ReflectionClass<TArg> $class
     *
     * @return ClassMetadata<TArg>
     * @throws \Throwable
     */
    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        if (\PHP_VERSION_ID >= 80400) {
            /** @var ClassMetadata<TArg> */
            return $this->toProxyClassMetadata($class, $types, $parser);
        }

        /** @var ClassMetadata<TArg> */
        return $this->toLazyInitializedClassMetadata($class, $types, $parser);
    }

    /**
     * @template TArg of object
     *
     * @param \ReflectionClass<TArg> $class
     *
     * @return ClassMetadata<TArg>
     * @throws \Throwable
     */
    private function toProxyClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        /** @var ClassMetadata<TArg> */
        return $this->metadata[$class->name] ??=
            (new \ReflectionClass(ClassMetadata::class))
                ->newLazyProxy(function () use ($class, $types, $parser): ClassMetadata {
                    $info = $this->reader->read($class);

                    $metadata = new ClassMetadata(
                        name: $info->name,
                        properties: $this->toPropertiesMetadata(
                            context: $class,
                            parent: $info,
                            properties: $info->properties,
                            types: $types,
                            parser: $parser,
                        ),
                        discriminator: $this->toOptionalDiscriminator(
                            context: $class,
                            parent: $info,
                            info: $info->discriminator,
                            types: $types,
                            parser: $parser,
                        ),
                        isNormalizeAsArray: $info->isNormalizeAsArray,
                        typeErrorMessage: $info->typeErrorMessage,
                        createdAt: $this->now(),
                    );

                    unset($this->metadata[$class->name]);

                    return $metadata;
                });
    }

    /**
     * @template TArg of object
     *
     * @param \ReflectionClass<TArg> $class
     *
     * @return ClassMetadata<TArg>
     * @throws \Throwable
     */
    private function toLazyInitializedClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        if (isset($this->metadata[$class->name])) {
            /** @var ClassMetadata<TArg> */
            return $this->metadata[$class->name];
        }

        $info = $this->reader->read($class);

        $this->metadata[$class->name] = $metadata = new ClassMetadata(
            name: $info->name,
            isNormalizeAsArray: $info->isNormalizeAsArray,
            typeErrorMessage: $info->typeErrorMessage,
            createdAt: $this->now(),
        );

        /** @phpstan-ignore-next-line : Allow readonly writing */
        $metadata->properties = $this->toPropertiesMetadata(
            context: $class,
            parent: $info,
            properties: $info->properties,
            types: $types,
            parser: $parser,
        );

        /** @phpstan-ignore-next-line : Allow readonly writing */
        $metadata->discriminator = $this->toOptionalDiscriminator(
            context: $class,
            parent: $info,
            info: $info->discriminator,
            types: $types,
            parser: $parser,
        );

        unset($this->metadata[$class->name]);

        /** @var ClassMetadata<TArg> */
        return $metadata;
    }

    /**
     * @param \ReflectionClass<object> $context
     * @param ClassInfo<object> $parent
     * @param iterable<mixed, PropertyInfo> $properties
     *
     * @return array<non-empty-string, PropertyMetadata>
     * @throws \Throwable
     */
    private function toPropertiesMetadata(
        \ReflectionClass $context,
        ClassInfo $parent,
        iterable $properties,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): array {
        $result = [];

        foreach ($properties as $property) {
            $result[$property->name] = $this->toPropertyMetadata($context, $parent, $property, $types, $parser);
        }

        return $result;
    }

    /**
     * @param \ReflectionClass<object> $context
     * @param ClassInfo<object> $parent
     *
     * @throws \Throwable
     */
    private function toPropertyMetadata(
        \ReflectionClass $context,
        ClassInfo $parent,
        PropertyInfo $property,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): PropertyMetadata {
        try {
            $read = $this->toTypeMetadata($context, $property->read, $types, $parser);
        } catch (TypeNotFoundException $e) {
            throw $this->toPropertyTypeException($e, $parent, $property, $property->read);
        }

        try {
            $write = $this->toTypeMetadata($context, $property->write, $types, $parser);
        } catch (TypeNotFoundException $e) {
            throw $this->toPropertyTypeException($e, $parent, $property, $property->write);
        }

        return new PropertyMetadata(
            name: $property->name,
            alias: $property->alias,
            read: $read,
            write: $write,
            default: $this->toOptionalDefaultValueMetadata($property->default),
            skip: $this->toConditionsMetadata($property->skip),
            typeErrorMessage: $property->typeErrorMessage,
            undefinedErrorMessage: $property->undefinedErrorMessage,
            createdAt: $this->now(),
        );
    }

    /**
     * @param ClassInfo<object> $class
     */
    private function toPropertyTypeException(
        TypeNotFoundException $e,
        ClassInfo $class,
        PropertyInfo $property,
        TypeInfo $type,
    ): PropertyTypeNotFoundException {
        $error = PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
            class: $class->name,
            property: $property->name,
            type: $e->type,
            previous: $e,
        );

        if ($type->source !== null) {
            $error->setSource($type->source->file, $type->source->line);
        }

        return $error;
    }

    /**
     * @param iterable<mixed, ConditionInfo> $conditions
     *
     * @return list<ConditionMetadata>
     */
    private function toConditionsMetadata(iterable $conditions): array
    {
        $result = [];

        foreach ($conditions as $condition) {
            $result[] = $this->toConditionMetadata($condition);
        }

        return $result;
    }

    private function toConditionMetadata(ConditionInfo $info): ConditionMetadata
    {
        return match (true) {
            $info instanceof NullConditionInfo => new NullConditionMetadata(
                createdAt: $this->now(),
            ),
            $info instanceof EmptyConditionInfo => new EmptyConditionMetadata(
                createdAt: $this->now(),
            ),
            $info instanceof ExpressionConditionInfo => new ExpressionConditionMetadata(
                expression: $this->createExpression(
                    expression: $info->expression,
                    names: [$info->context],
                ),
                variable: $info->context,
            ),
            default => throw new \InvalidArgumentException(\sprintf(
                'Unsupported type of condition "%s"',
                $info::class,
            )),
        };
    }

    private function toOptionalDefaultValueMetadata(?DefaultValueInfo $info): ?DefaultValueMetadata
    {
        if ($info === null) {
            return null;
        }

        return $this->toDefaultValueMetadata($info);
    }

    private function toDefaultValueMetadata(DefaultValueInfo $info): DefaultValueMetadata
    {
        return new DefaultValueMetadata(
            value: $info->value,
            createdAt: $this->now(),
        );
    }

    /**
     * @param \ReflectionClass<object> $context
     * @param ClassInfo<object> $parent
     *
     * @throws \Throwable
     */
    private function toOptionalDiscriminator(
        \ReflectionClass $context,
        ClassInfo $parent,
        ?DiscriminatorInfo $info,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ?DiscriminatorMetadata {
        if ($info === null) {
            return null;
        }

        return $this->toDiscriminator($context, $parent, $info, $types, $parser);
    }

    /**
     * @param \ReflectionClass<object> $context
     * @param ClassInfo<object> $parent
     *
     * @throws \Throwable
     */
    private function toDiscriminator(
        \ReflectionClass $context,
        ClassInfo $parent,
        DiscriminatorInfo $info,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): DiscriminatorMetadata {
        // TODO Customize discriminator errors

        return new DiscriminatorMetadata(
            field: $info->field,
            map: $this->toDiscriminatorMap($context, $info->map, $types, $parser),
            default: $this->toOptionalTypeMetadata($context, $info->default, $types, $parser),
            createdAt: $this->now(),
        );
    }

    /**
     * @param \ReflectionClass<object> $context
     * @param non-empty-array<non-empty-string, TypeInfo> $map
     *
     * @return non-empty-array<non-empty-string, TypeMetadata>
     * @throws \Throwable
     */
    private function toDiscriminatorMap(
        \ReflectionClass $context,
        array $map,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): array {
        $result = [];

        foreach ($map as $value => $type) {
            $result[$value] = $this->toTypeMetadata($context, $type, $types, $parser);
        }

        /** @var non-empty-array<non-empty-string, TypeMetadata> $result */
        return $result;
    }

    /**
     * @param \ReflectionClass<object> $context
     *
     * @throws \Throwable
     */
    private function toOptionalTypeMetadata(
        \ReflectionClass $context,
        ?TypeInfo $type,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ?TypeMetadata {
        if ($type === null) {
            return null;
        }

        return $this->toTypeMetadata($context, $type, $types, $parser);
    }

    /**
     * @param \ReflectionClass<object> $context
     *
     * @throws \Throwable
     */
    private function toTypeMetadata(
        \ReflectionClass $context,
        TypeInfo $info,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeMetadata {
        $statement = match (true) {
            $info instanceof RawTypeInfo => $parser->getStatementByDefinition($info->definition),
            $info instanceof ParsedTypeInfo => $info->statement,
            default => throw new \InvalidArgumentException(\sprintf(
                'Unsupported type info "%s"',
                $info::class,
            ))
        };

        $statement = $this->references->resolve($statement, $context);

        $type = $types->getTypeByStatement($statement);

        return new TypeMetadata(
            type: $type,
            statement: $statement,
            strict: $info->strict,
            createdAt: $this->now(),
        );
    }

    /**
     * @param non-empty-string $expression
     * @param list<non-empty-string> $names
     *
     * @throws ComposerPackageRequiredException
     */
    private function createExpression(string $expression, array $names): ParsedExpression
    {
        $parser = $this->getExpressionLanguage();

        return $parser->parse($expression, $names);
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private function createDefaultExpressionLanguage(): ExpressionLanguage
    {
        if (!\class_exists(ExpressionLanguage::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'symfony/expression-language',
                purpose: 'condition expressions support',
            );
        }

        return new ExpressionLanguage();
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private function getExpressionLanguage(): ExpressionLanguage
    {
        return $this->expression ??= $this->createDefaultExpressionLanguage();
    }
}
