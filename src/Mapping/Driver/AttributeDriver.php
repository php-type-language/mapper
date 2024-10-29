<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\SkipWhen;
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
        foreach ($reflection->getProperties() as $property) {
            $metadata = $class->getPropertyOrCreate($property->getName());

            // -----------------------------------------------------------------
            //  Apply property type
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, MapType::class);

            if ($attribute !== null) {
                $type = $this->createType($attribute->type, $property, $types, $parser);

                $metadata->setTypeInfo($type);
            }

            // -----------------------------------------------------------------
            //  Apply property name
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, MapName::class);

            if ($attribute !== null) {
                $metadata->setExportName($attribute->name);
            }

            // -----------------------------------------------------------------
            //  Apply skip condition
            // -----------------------------------------------------------------

            $attribute = $this->findPropertyAttribute($property, SkipWhen::class);

            if ($attribute !== null) {
                $expression = $this->createExpression($attribute->expr, [
                    'this',
                ]);

                $metadata->setSkipCondition($expression);
            }
        }
    }

    /**
     * @param non-empty-string $type
     *
     * @throws PropertyTypeNotFoundException
     * @throws \Throwable
     */
    private function createType(
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
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $class
     *
     * @return TAttribute|null
     */
    private function findPropertyAttribute(\ReflectionProperty $property, string $class): ?object
    {
        $attributes = $property->getAttributes($class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var TAttribute */
            return $attribute->newInstance();
        }

        return null;
    }
}
