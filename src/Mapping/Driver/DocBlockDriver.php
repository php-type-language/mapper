<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\ClassPropertyTypeDriver;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\PromotedPropertyTypeDriver;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reference\NativeReferencesReader;
use TypeLang\Mapper\Mapping\Reference\ReferencesReaderInterface;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\TypeResolver;
use TypeLang\PHPDoc\Parser;
use TypeLang\PHPDoc\Standard\ParamTagFactory;
use TypeLang\PHPDoc\Standard\VarTagFactory;
use TypeLang\PHPDoc\Tag\Factory\TagFactory;
use TypeLang\Reader\Exception\ReaderExceptionInterface;

final class DocBlockDriver extends Driver
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

    private readonly ReferencesReaderInterface $uses;

    private readonly TypeResolver $typeResolver;

    /**
     * @param non-empty-string $paramTagName
     * @param non-empty-string $varTagName
     *
     * @throws ComposerPackageRequiredException
     */
    public function __construct(
        private readonly DriverInterface $delegate = new ReflectionDriver(),
        string $paramTagName = self::DEFAULT_PARAM_TAG_NAME,
        string $varTagName = self::DEFAULT_VAR_TAG_NAME,
        ?ReferencesReaderInterface $references = null,
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
        $this->typeResolver = new TypeResolver();
        $this->uses = $this->createReferencesReader($references);
    }

    private function createReferencesReader(?ReferencesReaderInterface $reader): ReferencesReaderInterface
    {
        if ($reader !== null) {
            return $reader;
        }

        return new NativeReferencesReader();
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private static function assertKernelPackageIsInstalled(): void
    {
        if (!\class_exists(Parser::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc',
                for: 'docblock support'
            );
        }

        if (!\class_exists(ParamTagFactory::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc-standard-tags',
                for: '"@param" tag support'
            );
        }

        if (!\class_exists(VarTagFactory::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc-standard-tags',
                for: '"@var" tag support'
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

    /**
     * @throws ReaderExceptionInterface
     * @throws \ReflectionException
     * @throws TypeNotFoundException
     */
    public function getClassMetadata(\ReflectionClass $class, RepositoryInterface $types): ClassMetadata
    {
        $metadata = $this->delegate->getClassMetadata($class, $types);

        $uses = $this->uses->getUseStatements($class);

        foreach ($class->getProperties() as $reflection) {
            $property = $metadata->findProperty($reflection->getName())
                ?? new PropertyMetadata($reflection->getName());

            $statement = $this->findType($class, $property);

            if ($statement !== null) {
                $statement = $this->typeResolver->resolveWith($statement, $uses);

                try {
                    $type = $types->get($statement);
                } catch (TypeNotFoundException $e) {
                    throw TypeNotFoundException::fromPropertyType(
                        class: $metadata->getName(),
                        property: $property->getName(),
                        type: $e->getExpectedType(),
                        prev: $e,
                    );
                }

                $property->setType($type);
            }

            $metadata->addProperty($property);
        }

        $this->promotedProperties->cleanup();

        return $metadata;
    }
}
