<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\ClassMetadataLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\DiscriminatorMapClassMetadataLoader;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\ErrorMessagePropertyMetadataLoader;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\NamePropertyMetadataLoader;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\NormalizeAsArrayClassMetadataLoader;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\PropertyMetadataLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\SkipConditionsPropertyMetadataLoader;
use TypeLang\Mapper\Mapping\Driver\AttributeDriver\TypePropertyMetadataLoader;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class AttributeDriver extends LoadableDriver
{
    /**
     * @var list<ClassMetadataLoaderInterface>
     */
    private readonly array $classMetadataLoaders;

    /**
     * @var list<PropertyMetadataLoaderInterface>
     */
    private readonly array $propertyMetadataLoaders;

    public function __construct(
        DriverInterface $delegate = new NullDriver(),
        private readonly ?ExpressionLanguage $expression = null,
    ) {
        $this->classMetadataLoaders = $this->createClassLoaders();
        $this->propertyMetadataLoaders = $this->createPropertyLoaders();

        parent::__construct($delegate);
    }

    /**
     * @return list<PropertyMetadataLoaderInterface>
     */
    private function createPropertyLoaders(): array
    {
        return [
            new TypePropertyMetadataLoader(),
            new NamePropertyMetadataLoader(),
            new ErrorMessagePropertyMetadataLoader(),
            new SkipConditionsPropertyMetadataLoader($this->expression),
        ];
    }

    /**
     * @return list<ClassMetadataLoaderInterface>
     */
    private function createClassLoaders(): array
    {
        return [
            new NormalizeAsArrayClassMetadataLoader(),
            new DiscriminatorMapClassMetadataLoader(),
        ];
    }

    #[\Override]
    protected function load(
        \ReflectionClass $reflection,
        ClassMetadata $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        foreach ($this->classMetadataLoaders as $classMetadataLoader) {
            $classMetadataLoader->load(
                class: $reflection,
                metadata: $class,
                types: $types,
                parser: $parser,
            );
        }

        foreach ($reflection->getProperties() as $property) {
            $metadata = $class->getPropertyOrCreate($property->getName());

            foreach ($this->propertyMetadataLoaders as $propertyMetadataLoader) {
                $propertyMetadataLoader->load(
                    property: $property,
                    metadata: $metadata,
                    types: $types,
                    parser: $parser,
                );
            }
        }
    }
}
