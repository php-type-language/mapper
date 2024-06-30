<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta\Reader;

use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Meta\PropertyMetadata;
use TypeLang\Mapper\Meta\Reader\CodeReader\NativeUseStatementsReader;
use TypeLang\Mapper\Meta\Reader\CodeReader\UseStatementsReaderInterface;
use TypeLang\Mapper\Meta\Reader\DocBlockReader\ClassPropertyTypeReader;
use TypeLang\Mapper\Meta\Reader\DocBlockReader\PromotedPropertyTypeReader;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\TypeResolver;
use TypeLang\Parser\TypeResolverInterface;
use TypeLang\PHPDoc\Parser;
use TypeLang\PHPDoc\Standard\ParamTagFactory;
use TypeLang\PHPDoc\Standard\VarTagFactory;
use TypeLang\PHPDoc\Tag\Factory\TagFactory;
use TypeLang\Reader\Exception\ReaderExceptionInterface;

final class DocBlockReader extends Reader
{
    /**
     * @var non-empty-string
     */
    private const DEFAULT_PARAM_TAG_NAME = 'param';

    /**
     * @var non-empty-string
     */
    private const DEFAULT_VAR_TAG_NAME = 'var';

    private readonly PromotedPropertyTypeReader $promotedProperties;

    private readonly ClassPropertyTypeReader $classProperties;

    private readonly UseStatementsReaderInterface $uses;

    private readonly TypeResolverInterface $typeResolver;

    /**
     * @param non-empty-string $paramTagName
     * @param non-empty-string $varTagName
     * @throws ComposerPackageRequiredException
     */
    public function __construct(
        private readonly ReaderInterface $delegate = new ReflectionReader(),
        string $paramTagName = self::DEFAULT_PARAM_TAG_NAME,
        string $varTagName = self::DEFAULT_VAR_TAG_NAME,
        UseStatementsReaderInterface $uses = null,
    ) {
        self::assertKernelPackageIsInstalled();

        $parser = new Parser(new TagFactory([
            $paramTagName => new ParamTagFactory(),
            $varTagName => new VarTagFactory(),
        ]));

        $this->promotedProperties = new PromotedPropertyTypeReader($paramTagName, $parser);
        $this->classProperties = new ClassPropertyTypeReader($varTagName, $parser);
        $this->typeResolver = new TypeResolver();
        $this->uses = $this->createUseStatementsReader($uses);
    }

    private function createUseStatementsReader(?UseStatementsReaderInterface $reader): UseStatementsReaderInterface
    {
        if ($reader !== null) {
            return $reader;
        }

        return new NativeUseStatementsReader();
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
     * @throws \ReflectionException
     */
    private function findType(\ReflectionClass $class, PropertyMetadata $meta): ?TypeStatement
    {
        $property = $meta->getReflection($class);

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
    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        $metadata = $this->delegate->getClassMetadata($class, $types);

        $uses = $this->uses->getUseStatements($class);

        foreach ($class->getProperties() as $reflection) {
            $property = $metadata->findPropertyByName($reflection->getName())
                ?? new PropertyMetadata($reflection->getName());

            $type = $this->findType($class, $property);

            if ($type !== null) {
                $type = $this->typeResolver->resolveWith($type, $uses);

                $property = $property->withType(
                    type: $types->get($type),
                    statement: $type,
                );
            }

            $metadata = $metadata->withAddedProperty($property);
        }

        $this->promotedProperties->cleanup();

        return $metadata;
    }
}
