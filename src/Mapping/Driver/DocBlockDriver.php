<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Definition\PropertyTypeNotFoundException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\ClassPropertyTypeDriver;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\PromotedPropertyTypeDriver;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\PHPDoc\Parser;
use TypeLang\PHPDoc\Standard\ParamTagFactory;
use TypeLang\PHPDoc\Standard\VarTagFactory;
use TypeLang\PHPDoc\Tag\Factory\TagFactory;

final class DocBlockDriver extends LoadableDriver
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_PARAM_TAG_NAME = 'param';

    /**
     * @var non-empty-string
     */
    private const DEFAULT_VAR_TAG_NAME = 'var';

    private readonly PromotedPropertyTypeDriver $promotedProperties;

    private readonly ClassPropertyTypeDriver $classProperties;

    /**
     * @param non-empty-string $paramTagName
     * @param non-empty-string $varTagName
     *
     * @throws ComposerPackageRequiredException
     */
    public function __construct(
        string $paramTagName = self::DEFAULT_PARAM_TAG_NAME,
        string $varTagName = self::DEFAULT_VAR_TAG_NAME,
        DriverInterface $delegate = new NullDriver(),
    ) {
        self::assertKernelPackageIsInstalled();

        $parser = new Parser(new TagFactory([
            $paramTagName => new ParamTagFactory(),
            $varTagName => new VarTagFactory(),
        ]));

        $this->classProperties = new ClassPropertyTypeDriver(
            varTagName: $varTagName,
            parser: $parser,
        );

        $this->promotedProperties = new PromotedPropertyTypeDriver(
            paramTagName: $paramTagName,
            classProperties: $this->classProperties,
            parser: $parser,
        );

        parent::__construct($delegate);
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private static function assertKernelPackageIsInstalled(): void
    {
        if (!\class_exists(Parser::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc',
                purpose: 'docblock support'
            );
        }

        if (!\class_exists(ParamTagFactory::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc-standard-tags',
                purpose: '"@param" tag support'
            );
        }

        if (!\class_exists(VarTagFactory::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc-standard-tags',
                purpose: '"@var" tag support'
            );
        }
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @throws \ReflectionException
     */
    private function findType(\ReflectionClass $class, PropertyMetadata $meta): ?TypeStatement
    {
        $property = $class->getProperty($meta->getName());

        if ($property->isPromoted()) {
            return $this->promotedProperties->findType($property, $meta);
        }

        return $this->classProperties->findType($property);
    }

    protected function load(\ReflectionClass $reflection, ClassMetadata $class, RepositoryInterface $types): void
    {
        foreach ($class->getProperties() as $reflectionProperty) {
            $property = $class->getPropertyOrCreate($reflectionProperty->getName());

            $statement = $this->findType($reflection, $property);

            if ($statement !== null) {
                try {
                    $type = $types->getByStatement($statement, $reflection);
                } catch (TypeNotFoundException $e) {
                    throw PropertyTypeNotFoundException::becauseTypeOfPropertyNotDefined(
                        class: $class->getName(),
                        property: $property->getName(),
                        type: $e->getType(),
                        previous: $e,
                    );
                }

                $property->setType($type);
            }

            $class->addProperty($property);
        }

        $this->promotedProperties->cleanup();
    }
}
