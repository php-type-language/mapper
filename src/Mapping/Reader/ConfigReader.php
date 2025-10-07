<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\AliasPropertyConfigLoader;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\ClassConfigLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\DiscriminatorMapClassConfigLoader;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\ErrorMessagePropertyConfigLoader;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\NormalizeAsArrayClassConfigLoader;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\PropertyConfigLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\SchemaValidator;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\SkipConditionsPropertyConfigLoader;
use TypeLang\Mapper\Mapping\Reader\ConfigReader\TypePropertyConfigLoader;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;

/**
 * @phpstan-import-type ClassConfigType from SchemaValidator
 *
 * @template-extends Reader<ClassConfigLoaderInterface, PropertyConfigLoaderInterface>
 */
abstract class ConfigReader extends Reader
{
    private readonly ?SchemaValidator $validator;

    public function __construct(
        ReaderInterface $delegate = new ReflectionReader(),
    ) {
        parent::__construct($delegate);

        $this->validator = $this->createSchemaValidator();
    }

    #[\Override]
    protected function createClassLoaders(): array
    {
        return [
            new NormalizeAsArrayClassConfigLoader(),
            new DiscriminatorMapClassConfigLoader(),
        ];
    }

    #[\Override]
    protected function createPropertyLoaders(): array
    {
        return [
            new TypePropertyConfigLoader(),
            new AliasPropertyConfigLoader(),
            new ErrorMessagePropertyConfigLoader(),
            new SkipConditionsPropertyConfigLoader(),
        ];
    }

    private function createSchemaValidator(): ?SchemaValidator
    {
        if (SchemaValidator::isAvailable()) {
            return new SchemaValidator();
        }

        return null;
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return array<array-key, mixed>|null
     */
    abstract protected function load(\ReflectionClass $class): ?array;

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return ClassConfigType|null
     */
    private function loadAndValidate(\ReflectionClass $class): ?array
    {
        $config = $this->load($class);

        if ($config === null) {
            return null;
        }

        $this->validator?->validateOrFail($class->name, $config);

        /** @var ClassConfigType */
        return $config;
    }

    public function read(\ReflectionClass $class, TypeParserInterface $parser): ClassInfo
    {
        $info = parent::read($class, $parser);

        $config = $this->loadAndValidate($class);

        if ($config !== null) {
            $this->readFromConfig($info, $config);
        }

        return $info;
    }

    /**
     * @param ClassInfo<object> $classInfo
     * @param ClassConfigType $classConfig
     */
    private function readFromConfig(ClassInfo $classInfo, array $classConfig): void
    {
        foreach ($this->classLoaders as $classLoader) {
            $classLoader->load($classInfo, $classConfig);
        }

        $classConfig['properties'] ??= [];

        foreach ($classConfig['properties'] as $propertyName => $propertyConfig) {
            // Prepare: Normalize config in case of config contains string
            if (\is_string($propertyConfig)) {
                $propertyConfig = ['type' => $propertyConfig];
            }

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_string($propertyName));

            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_array($propertyConfig));

            $propertyInfo = $classInfo->getPropertyOrCreate($propertyName);

            foreach ($this->propertyLoaders as $propertyLoader) {
                $propertyLoader->load($propertyInfo, $propertyConfig);
            }
        }
    }
}
