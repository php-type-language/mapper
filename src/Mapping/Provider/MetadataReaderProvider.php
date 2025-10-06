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
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class MetadataReaderProvider implements ProviderInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private ?ExpressionLanguage $expression = null,
        private readonly ?ClockInterface $clock = null,
    ) {}

    private function now(): ?int
    {
        $now = $this->clock?->now();

        return $now?->getTimestamp();
    }

    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        $info = $this->reader->read($class);

        return $this->toClassMetadata($info, $types, $parser);
    }

    /**
     * @template T of object
     *
     * @param ClassInfo<T> $class
     *
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private function toClassMetadata(
        ClassInfo $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        return new ClassMetadata(
            name: $class->name,
            properties: $this->toPropertiesMetadata($class, $class->properties, $types, $parser),
            discriminator: $this->toOptionalDiscriminator($class, $class->discriminator, $types, $parser),
            isNormalizeAsArray: $class->isNormalizeAsArray,
            typeErrorMessage: $class->typeErrorMessage,
            createdAt: $this->now(),
        );
    }

    /**
     * @param ClassInfo<object> $parent
     * @param iterable<mixed, PropertyInfo> $properties
     *
     * @return array<non-empty-string, PropertyMetadata>
     * @throws \Throwable
     */
    private function toPropertiesMetadata(
        ClassInfo $parent,
        iterable $properties,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): array {
        $result = [];

        foreach ($properties as $property) {
            $result[$property->name] = $this->toPropertyMetadata($parent, $property, $types, $parser);
        }

        return $result;
    }

    /**
     * @param ClassInfo<object> $parent
     *
     * @throws \Throwable
     */
    private function toPropertyMetadata(
        ClassInfo $parent,
        PropertyInfo $property,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): PropertyMetadata {
        try {
            $read = $this->toTypeMetadata($property->read, $types, $parser);
        } catch (TypeNotFoundException $e) {
            throw $this->toPropertyTypeException($e, $parent, $property, $property->read);
        }

        try {
            $write = $this->toTypeMetadata($property->write, $types, $parser);
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
     * @param ClassInfo<object> $parent
     *
     * @throws \Throwable
     */
    private function toOptionalDiscriminator(
        ClassInfo $parent,
        ?DiscriminatorInfo $info,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ?DiscriminatorMetadata {
        if ($info === null) {
            return null;
        }

        return $this->toDiscriminator($parent, $info, $types, $parser);
    }

    /**
     * @param ClassInfo<object> $parent
     *
     * @throws \Throwable
     */
    private function toDiscriminator(
        ClassInfo $parent,
        DiscriminatorInfo $info,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): DiscriminatorMetadata {
        // TODO Customize discriminator errors

        return new DiscriminatorMetadata(
            field: $info->field,
            map: $this->toDiscriminatorMap($info->map, $types, $parser),
            default: $this->toOptionalTypeMetadata($info->default, $types, $parser),
            createdAt: $this->now(),
        );
    }

    /**
     * @param non-empty-array<non-empty-string, TypeInfo> $map
     *
     * @return non-empty-array<non-empty-string, TypeMetadata>
     * @throws \Throwable
     */
    private function toDiscriminatorMap(
        array $map,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): array {
        $result = [];

        foreach ($map as $value => $type) {
            $result[$value] = $this->toTypeMetadata($type, $types, $parser);
        }

        /** @var non-empty-array<non-empty-string, TypeMetadata> $result */
        return $result;
    }

    /**
     * @throws \Throwable
     */
    private function toOptionalTypeMetadata(
        ?TypeInfo $type,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ?TypeMetadata {
        if ($type === null) {
            return null;
        }

        return $this->toTypeMetadata($type, $types, $parser);
    }

    /**
     * @throws \Throwable
     */
    private function toTypeMetadata(
        TypeInfo $info,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeMetadata {
        $statement = $parser->getStatementByDefinition($info->definition);
        $type = $types->getTypeByStatement($statement);

        return new TypeMetadata(
            type: $type,
            statement: $statement,
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
