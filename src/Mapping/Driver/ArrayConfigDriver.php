<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\SchemaValidator;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMapMetadata;
use TypeLang\Mapper\Mapping\Metadata\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

abstract class ArrayConfigDriver extends LoadableDriver
{
    private static ?bool $supportsSchemaValidation = null;

    public function __construct(
        DriverInterface $delegate = new NullDriver(),
        private ?ExpressionLanguage $expression = null,
    ) {
        self::$supportsSchemaValidation ??= SchemaValidator::isAvailable();

        parent::__construct($delegate);
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return array<array-key, mixed>|null
     */
    abstract protected function getConfiguration(\ReflectionClass $class): ?array;

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return array{
     *     normalize_as_array?: bool,
     *     discriminator?: array{
     *         field: non-empty-string,
     *         map: array<non-empty-string, non-empty-string>,
     *         otherwise?: non-empty-string,
     *     },
     *     properties?: array<non-empty-string, non-empty-string|array{
     *         name?: non-empty-string,
     *         type?: non-empty-string,
     *         skip?: 'null'|'empty'|non-empty-string|list<'null'|'empty'|non-empty-string>,
     *         ...
     *     }>
     * }|null
     * @throws \InvalidArgumentException
     */
    private function getConfigurationAndValidate(\ReflectionClass $class): ?array
    {
        $config = $this->getConfiguration($class);

        if ($config === null) {
            return null;
        }

        $this->validate($class->getName(), $config);

        // @phpstan-ignore-next-line
        return $config;
    }

    /**
     * @param non-empty-string $path
     * @param array<array-key, mixed> $config
     *
     * @throws \InvalidArgumentException
     */
    private function validate(string $path, array $config): void
    {
        if (self::$supportsSchemaValidation !== true) {
            return;
        }

        $validator = new SchemaValidator();
        $validator->validateOrFail($path, $config);
    }

    protected function load(
        \ReflectionClass $reflection,
        ClassMetadata $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $classConfig = $this->getConfigurationAndValidate($reflection);

        if ($classConfig === null) {
            return;
        }

        // ---------------------------------------------------------------------
        //  > start: Normalize as array
        // ---------------------------------------------------------------------

        if (\array_key_exists('normalize_as_array', $classConfig)) {
            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_bool($classConfig['normalize_as_array']));

            $class->isNormalizeAsArray = $classConfig['normalize_as_array'];
        }

        // ---------------------------------------------------------------------
        //  end: Normalize as array
        //  > start: Discriminator Map
        // ---------------------------------------------------------------------

        if (\array_key_exists('discriminator', $classConfig)) {
            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_array($classConfig['discriminator']));

            $discriminatorConfig = $classConfig['discriminator'];

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\array_key_exists('field', $discriminatorConfig));

            $discriminator = new DiscriminatorMapMetadata(
                field: $discriminatorConfig['field'],
            );

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\array_key_exists('map', $discriminatorConfig));

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_array($discriminatorConfig['map']));

            foreach ($discriminatorConfig['map'] as $discriminatorValue => $discriminatorType) {
                // @phpstan-ignore-next-line : Additional DbC invariant
                assert(\is_string($discriminatorValue));

                // @phpstan-ignore-next-line : Additional DbC invariant
                assert(\is_string($discriminatorType));

                $discriminator->addType(
                    fieldValue: $discriminatorValue,
                    type: $this->createDiscriminatorType(
                        type: $discriminatorType,
                        class: $reflection,
                        types: $types,
                        parser: $parser,
                    ),
                );
            }

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\array_key_exists('otherwise', $discriminatorConfig));

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_string($discriminatorConfig['otherwise']));

            $discriminator->setDefaultType($this->createDiscriminatorType(
                type: $discriminatorConfig['otherwise'],
                class: $reflection,
                types: $types,
                parser: $parser,
            ));
        }

        // ---------------------------------------------------------------------
        //  end: Discriminator Map
        //  > start: Properties
        // ---------------------------------------------------------------------

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_array($classConfig['properties'] ?? []));

        foreach ($classConfig['properties'] ?? [] as $propertyName => $propertyConfig) {
            // Prepare: Normalize config in case of config contains string
            if (\is_string($propertyConfig)) {
                $propertyConfig = ['type' => $propertyConfig];
            }

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_string($propertyName));

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_array($propertyConfig));

            $metadata = $class->getPropertyOrCreate($propertyName);

            // -----------------------------------------------------------------
            //  start: Property Type
            // -----------------------------------------------------------------

            if (\array_key_exists('type', $propertyConfig)) {
                // @phpstan-ignore-next-line : Additional DbC invariant
                assert(\is_string($propertyConfig['type']));

                $metadata->setTypeInfo($this->createPropertyType(
                    class: $reflection,
                    propertyName: $propertyName,
                    propertyType: $propertyConfig['type'],
                    types: $types,
                    parser: $parser,
                ));
            }

            // -----------------------------------------------------------------
            //  end: Property Type
            //  > start: Property Name
            // -----------------------------------------------------------------

            if (\array_key_exists('name', $propertyConfig)) {
                // @phpstan-ignore-next-line : Additional DbC invariant
                assert(\is_string($propertyConfig['name']));

                $metadata->setExportName($propertyConfig['name']);
            }

            // -----------------------------------------------------------------
            //  end: Property Name
            //  > start: Property Skip Behaviour
            // -----------------------------------------------------------------

            if (\array_key_exists('skip', $propertyConfig)) {
                if (\is_string($propertyConfig['skip'])) {
                    $propertyConfig['skip'] = [$propertyConfig['skip']];
                }

                // @phpstan-ignore-next-line : Additional DbC invariant
                assert(\is_array($propertyConfig['skip']));

                foreach ($propertyConfig['skip'] as $propertyConfigSkip) {
                    // @phpstan-ignore-next-line : Additional DbC invariant
                    assert(\is_string($propertyConfigSkip));

                    $metadata->addSkipCondition(match ($propertyConfigSkip) {
                        'null' => new NullConditionMetadata(),
                        'empty' => new EmptyConditionMetadata(),
                        default => new ExpressionConditionMetadata(
                            expression: $this->createExpression($propertyConfigSkip, [
                                ExpressionConditionMetadata::DEFAULT_CONTEXT_VARIABLE_NAME,
                            ]),
                            context: ExpressionConditionMetadata::DEFAULT_CONTEXT_VARIABLE_NAME,
                        )
                    });
                }
            }

            // -----------------------------------------------------------------
            //  end: Property Skip Behaviour
            // -----------------------------------------------------------------
        }

        // ---------------------------------------------------------------------
        //  end: Properties
        // ---------------------------------------------------------------------
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param non-empty-string $propertyName
     * @param non-empty-string $propertyType
     *
     * @throws PropertyTypeNotFoundException in case of property type not found
     * @throws \Throwable in case of internal error occurs
     */
    private function createPropertyType(
        \ReflectionClass $class,
        string $propertyName,
        string $propertyType,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeMetadata {
        $statement = $parser->getStatementByDefinition($propertyType);

        try {
            $instance = $types->getTypeByStatement($statement, $class);
        } catch (TypeNotFoundException $e) {
            throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                class: $class->getName(),
                property: $propertyName,
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
     * @param non-empty-string $expression
     * @param list<non-empty-string> $names
     *
     * @throws ComposerPackageRequiredException
     */
    private function createExpression(string $expression, array $names): ParsedExpression
    {
        $parser = ($this->expression ??= $this->createDefaultExpressionLanguage());

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
}
