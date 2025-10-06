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
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorMetadata\DiscriminatorMapPrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorPrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata\DefaultValuePrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototypeSet;
use TypeLang\Mapper\Mapping\Metadata\ClassPrototype;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\EmptyConditionPrototype;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\ExpressionConditionPrototype;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\Condition\NullConditionPrototype;
use TypeLang\Mapper\Mapping\Metadata\ConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ConditionPrototype;
use TypeLang\Mapper\Mapping\Metadata\ConditionPrototypeSet;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypePrototype;
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
     * @param ClassPrototype<T> $proto
     *
     * @return ClassMetadata<T>
     * @throws \Throwable
     */
    private function toClassMetadata(
        ClassPrototype $proto,
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
     * @param ClassPrototype<object> $parent
     *
     * @return array<non-empty-string, PropertyMetadata>
     * @throws \Throwable
     */
    private function toPropertiesMetadata(
        ClassPrototype $parent,
        PropertyPrototypeSet $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): array {
        $result = [];

        foreach ($proto as $property) {
            $result[$property->name] = $this->toPropertyMetadata($parent, $property, $types, $parser);
        }

        return $result;
    }

    /**
     * @param ClassPrototype<object> $parent
     *
     * @throws \Throwable
     */
    private function toPropertyMetadata(
        ClassPrototype $parent,
        PropertyPrototype $proto,
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
     * @param ClassPrototype<object> $class
     */
    private function toPropertyTypeException(
        TypeNotFoundException $e,
        ClassPrototype $class,
        PropertyPrototype $property,
        TypePrototype $proto,
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
     * @return list<ConditionMetadata>
     */
    private function toConditionsMetadata(ConditionPrototypeSet $proto): array
    {
        $result = [];

        foreach ($proto as $condition) {
            $result[] = $this->toConditionMetadata($condition);
        }

        return $result;
    }

    private function toConditionMetadata(ConditionPrototype $proto): ConditionMetadata
    {
        return match (true) {
            $proto instanceof NullConditionPrototype => new NullConditionMetadata(
                createdAt: $this->now(),
            ),
            $proto instanceof EmptyConditionPrototype => new EmptyConditionMetadata(
                createdAt: $this->now(),
            ),
            $proto instanceof ExpressionConditionPrototype => new ExpressionConditionMetadata(
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

    private function toOptionalDefaultValueMetadata(?DefaultValuePrototype $proto): ?DefaultValueMetadata
    {
        if ($proto === null) {
            return null;
        }

        return $this->toDefaultValueMetadata($proto);
    }

    private function toDefaultValueMetadata(DefaultValuePrototype $proto): DefaultValueMetadata
    {
        return new DefaultValueMetadata(
            value: $proto->value,
            createdAt: $this->now(),
        );
    }

    /**
     * @param ClassPrototype<object> $parent
     *
     * @throws \Throwable
     */
    private function toOptionalDiscriminator(
        ClassPrototype $parent,
        ?DiscriminatorPrototype $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ?DiscriminatorMetadata {
        if ($proto === null) {
            return null;
        }

        return $this->toDiscriminator($parent, $proto, $types, $parser);
    }

    /**
     * @param ClassPrototype<object> $parent
     *
     * @throws \Throwable
     */
    private function toDiscriminator(
        ClassPrototype $parent,
        DiscriminatorPrototype $proto,
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
     * @return non-empty-array<non-empty-string, TypeMetadata>
     * @throws \Throwable
     */
    private function toDiscriminatorMap(
        DiscriminatorMapPrototype $proto,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): array {
        $result = [];

        foreach ($proto as $value => $map) {
            $result[$value] = $this->toTypeMetadata($map, $types, $parser);
        }

        /** @var non-empty-array<non-empty-string, TypeMetadata> $result */
        return $result;
    }

    /**
     * @throws \Throwable
     */
    private function toOptionalTypeMetadata(
        ?TypePrototype $proto,
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
        TypePrototype $info,
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
