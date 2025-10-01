<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\ClassConfigLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\ErrorMessagePropertyConfigLoader;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\AliasPropertyConfigLoader;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\PropertyConfigLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\SchemaValidator;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\SkipConditionsPropertyConfigLoader;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\TypePropertyConfigLoader;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\DiscriminatorMapClassConfigLoader;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\NormalizeAsArrayClassConfigLoader;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

/**
 * @phpstan-type PropertyConfigType array{
 *     name?: non-empty-string,
 *     type?: non-empty-string,
 *     skip?: 'null'|'empty'|non-empty-string|list<'null'|'empty'|non-empty-string>,
 *     type_error_message?: non-empty-string,
 *     undefined_error_message?: non-empty-string,
 *     ...
 * }
 * @phpstan-type ClassDiscriminatorConfigType array{
 *     field: non-empty-string,
 *     map: array<non-empty-string, non-empty-string>,
 *     otherwise?: non-empty-string,
 * }
 * @phpstan-type ClassConfigType array{
 *     normalize_as_array?: bool,
 *     discriminator?: ClassDiscriminatorConfigType,
 *     properties?: array<non-empty-string, non-empty-string|PropertyConfigType>
 * }
 */
abstract class ArrayConfigDriver extends LoadableDriver
{
    private static ?bool $supportsSchemaValidation = null;

    /**
     * @var list<ClassConfigLoaderInterface>
     */
    private readonly array $classConfigLoaders;

    /**
     * @var list<PropertyConfigLoaderInterface>
     */
    private readonly array $propertyConfigLoaders;

    public function __construct(
        DriverInterface $delegate = new NullDriver(),
        private readonly ?ExpressionLanguage $expression = null,
    ) {
        self::$supportsSchemaValidation ??= SchemaValidator::isAvailable();

        $this->classConfigLoaders = $this->createClassLoaders();
        $this->propertyConfigLoaders = $this->createPropertyLoaders();

        parent::__construct($delegate);
    }

    /**
     * @return list<ClassConfigLoaderInterface>
     */
    private function createClassLoaders(): array
    {
        return [
            new NormalizeAsArrayClassConfigLoader(),
            new DiscriminatorMapClassConfigLoader(),
        ];
    }

    /**
     * @return list<PropertyConfigLoaderInterface>
     */
    private function createPropertyLoaders(): array
    {
        return [
            new TypePropertyConfigLoader(),
            new AliasPropertyConfigLoader(),
            new ErrorMessagePropertyConfigLoader(),
            new SkipConditionsPropertyConfigLoader($this->expression),
        ];
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
     * @return ClassConfigType|null
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

        foreach ($this->classConfigLoaders as $classConfigLoader) {
            $classConfigLoader->load(
                config: $classConfig,
                class: $reflection,
                metadata: $class,
                types: $types,
                parser: $parser,
            );
        }

        $classConfig['properties'] ??= [];

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_array($classConfig['properties']));

        foreach ($classConfig['properties'] as $propertyName => $propertyConfig) {
            // Prepare: Normalize config in case of config contains string
            if (\is_string($propertyConfig)) {
                $propertyConfig = ['type' => $propertyConfig];
            }

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_string($propertyName));

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_array($propertyConfig));

            $metadata = $class->getPropertyOrCreate($propertyName);

            foreach ($this->propertyConfigLoaders as $propertyConfigLoader) {
                $propertyConfigLoader->load(
                    config: $propertyConfig,
                    property: new \ReflectionProperty($class, $propertyName),
                    metadata: $metadata,
                    types: $types,
                    parser: $parser,
                );
            }
        }
    }
}
