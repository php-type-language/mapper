<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMapMetadata;
use TypeLang\Mapper\Mapping\Metadata\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class AttributeDriver extends LoadableDriver
{
    public function __construct(
        DriverInterface $delegate = new NullDriver(),
        private ?ExpressionLanguage $expression = null,
    ) {
        parent::__construct($delegate);
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private function getExpressionLanguage(): ExpressionLanguage
    {
        return $this->expression ??= $this->createDefaultExpressionLanguage();
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

    #[\Override]
    protected function load(
        \ReflectionClass $reflection,
        ClassMetadata $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        // -----------------------------------------------------------------
        //  Apply normalization logic
        // -----------------------------------------------------------------

        $attribute = $this->findClassAttribute($reflection, NormalizeAsArray::class);

        if ($attribute !== null) {
            $class->shouldNormalizeAsArray($attribute->enabled);
        }

        foreach ($reflection->getProperties() as $property) {
            $metadata = $class->getPropertyOrCreate($property->getName());

            // -----------------------------------------------------------------
            //  Apply property type
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, MapType::class);
            if ($attribute !== null) {
                $metadata->setTypeInfo($this->createPropertyType(
                    type: $attribute->type,
                    property: $property,
                    types: $types,
                    parser: $parser,
                ));
            }

            // -----------------------------------------------------------------
            //  Apply property name
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, MapName::class);
            if ($attribute !== null) {
                $metadata->setExportName($attribute->name);
            }

            // -----------------------------------------------------------------
            //  Apply skip conditions
            // -----------------------------------------------------------------

            $conditions = $this->getAllPropertyAttributes($property, SkipWhen::class);
            foreach ($conditions as $condition) {
                $metadata->addSkipCondition(new ExpressionConditionMetadata(
                    expression: $this->createExpression($condition->expr, [
                        $condition->context,
                    ]),
                    context: $condition->context,
                ));
            }

            $condition = $this->findPropertyAttribute($property, SkipWhenEmpty::class);
            if ($condition !== null) {
                $metadata->addSkipCondition(new EmptyConditionMetadata());
            }

            $condition = $this->findPropertyAttribute($property, SkipWhenNull::class);
            if ($condition !== null) {
                $metadata->addSkipCondition(new NullConditionMetadata());
            }
        }

        // -----------------------------------------------------------------
        //  Apply discriminator map
        // -----------------------------------------------------------------

        $attribute = $this->findClassAttribute($reflection, DiscriminatorMap::class);
        if ($attribute !== null) {
            $mapping = [];
            $default = null;

            foreach ($attribute->map as $mappedValue => $mappedType) {
                $mapping[$mappedValue] = $this->createDiscriminatorType(
                    type: $mappedType,
                    class: $reflection,
                    types: $types,
                    parser: $parser,
                );
            }

            if ($attribute->otherwise !== null) {
                $default = $this->createDiscriminatorType(
                    type: $attribute->otherwise,
                    class: $reflection,
                    types: $types,
                    parser: $parser,
                );
            }

            $class->setDiscriminator(new DiscriminatorMapMetadata(
                field: $attribute->field,
                map: $mapping,
                default: $default,
            ));
        }
    }

    /**
     * @param non-empty-string $type
     *
     * @throws PropertyTypeNotFoundException
     * @throws \Throwable
     */
    private function createPropertyType(
        string $type,
        \ReflectionProperty $property,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeMetadata {
        $statement = $parser->getStatementByDefinition($type);

        $class = $property->getDeclaringClass();

        try {
            $instance = $types->getTypeByStatement($statement, $class);
        } catch (TypeNotFoundException $e) {
            throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                class: $class->getName(),
                property: $property->getName(),
                type: $e->getType(),
                previous: $e,
            );
        }

        return new TypeMetadata($instance, $statement);
    }

    /**
     * @param non-empty-string $type
     * @param \ReflectionClass<object> $class
     *
     * @throws PropertyTypeNotFoundException
     * @throws \Throwable
     */
    private function createDiscriminatorType(
        string $type,
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeMetadata {
        $statement = $parser->getStatementByDefinition($type);

        // TODO Add custom "discriminator type exception"
        $instance = $types->getTypeByStatement($statement, $class);

        return new TypeMetadata($instance, $statement);
    }

    /**
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $attr
     *
     * @return TAttribute|null
     */
    private function findPropertyAttribute(\ReflectionProperty $property, string $attr): ?object
    {
        $attributes = $property->getAttributes($attr, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var TAttribute */
            return $attribute->newInstance();
        }

        return null;
    }

    /**
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $attr
     *
     * @return iterable<array-key, TAttribute>
     */
    private function getAllPropertyAttributes(\ReflectionProperty $property, string $attr): iterable
    {
        $attributes = $property->getAttributes($attr, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }

    /**
     * @template TAttribute of object
     *
     * @param \ReflectionClass<object> $class
     * @param class-string<TAttribute> $attr
     *
     * @return TAttribute|null
     */
    private function findClassAttribute(\ReflectionClass $class, string $attr): ?object
    {
        $attributes = $class->getAttributes($attr, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var TAttribute */
            return $attribute->newInstance();
        }

        return null;
    }
}
