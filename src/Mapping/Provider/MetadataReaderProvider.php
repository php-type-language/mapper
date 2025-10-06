<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use Psr\Clock\ClockInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\ConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ConditionInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;
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
     * @param ClassInfo<T> $proto
     *
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private function toClassMetadata(
        ClassInfo $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        return new ClassMetadata(
            name: $proto->name,
            properties: $this->toPropertiesMetadata($proto, $proto->properties, $types, $parser),
            discriminator: $this->toOptionalDiscriminator($proto, $proto->discriminator, $types, $parser),
            isNormalizeAsArray: $proto->isNormalizeAsArray,
            typeErrorMessage: $proto->typeErrorMessage,
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
        PropertyInfo $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): PropertyMetadata {
        try {
            $read = $this->toTypeMetadata($proto->read, $types, $parser);
        } catch (TypeNotFoundException $e) {
            throw $this->toPropertyTypeException($e, $parent, $proto, $proto->read);
        }

        try {
            $write = $this->toTypeMetadata($proto->write, $types, $parser);
        } catch (TypeNotFoundException $e) {
            throw $this->toPropertyTypeException($e, $parent, $proto, $proto->write);
        }

        return new PropertyMetadata(
            name: $proto->name,
            alias: $proto->alias,
            read: $read,
            write: $write,
            default: $this->toOptionalDefaultValueMetadata($proto->default),
            skip: $this->toConditionsMetadata($proto->skip),
            typeErrorMessage: $proto->typeErrorMessage,
            undefinedErrorMessage: $proto->undefinedErrorMessage,
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
        TypeInfo $proto,
    ): PropertyTypeNotFoundException {
        $error = PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
            class: $class->name,
            property: $property->name,
            type: $e->type,
            previous: $e,
        );

        if ($proto->source !== null) {
            $error->setSource($proto->source->file, $proto->source->line);
        }

        return $error;
    }

    /**
     * @param iterable<mixed, ConditionInfo> $conditions
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

    private function toConditionMetadata(ConditionInfo $proto): ConditionMetadata
    {
        return match (true) {
            $proto instanceof NullConditionInfo => new NullConditionMetadata(
                createdAt: $this->now(),
            ),
            $proto instanceof EmptyConditionInfo => new EmptyConditionMetadata(
                createdAt: $this->now(),
            ),
            $proto instanceof ExpressionConditionInfo => new ExpressionConditionMetadata(
                expression: $this->createExpression(
                    expression: $proto->expression,
                    names: [$proto->context],
                ),
                variable: $proto->context,
            ),
            default => throw new \InvalidArgumentException(\sprintf(
                'Unsupported type of condition "%s"',
                $proto::class,
            )),
        };
    }

    private function toOptionalDefaultValueMetadata(?DefaultValueInfo $proto): ?DefaultValueMetadata
    {
        if ($proto === null) {
            return null;
        }

        return $this->toDefaultValueMetadata($proto);
    }

    private function toDefaultValueMetadata(DefaultValueInfo $proto): DefaultValueMetadata
    {
        return new DefaultValueMetadata(
            value: $proto->value,
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
        ?DiscriminatorInfo $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ?DiscriminatorMetadata {
        if ($proto === null) {
            return null;
        }

        return $this->toDiscriminator($parent, $proto, $types, $parser);
    }

    /**
     * @param ClassInfo<object> $parent
     *
     * @throws \Throwable
     */
    private function toDiscriminator(
        ClassInfo $parent,
        DiscriminatorInfo $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): DiscriminatorMetadata {
        // TODO Customize discriminator errors

        return new DiscriminatorMetadata(
            field: $proto->field,
            map: $this->toDiscriminatorMap($proto->map, $types, $parser),
            default: $this->toOptionalTypeMetadata($proto->default, $types, $parser),
            createdAt: $this->now(),
        );
    }

    /**
     * @param non-empty-array<non-empty-string, TypeInfo> $map
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
        ?TypeInfo $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ?TypeMetadata {
        if ($proto === null) {
            return null;
        }

        return $this->toTypeMetadata($proto, $types, $parser);
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
                purpose: 'expressions support',
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
