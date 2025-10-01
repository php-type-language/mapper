<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\ClassConfigLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver\PropertyConfigLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\ClassDocBlockLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\PropertyDocBlockLoaderInterface;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\Reader\ParamTagReader;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\Reader\VarTagReader;
use TypeLang\Mapper\Mapping\Driver\DocBlockDriver\TypePropertyDocBlockLoader;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Parser;
use TypeLang\PHPDoc\Parser as DocBlockParser;
use TypeLang\PHPDoc\Standard\ParamTagFactory;
use TypeLang\PHPDoc\Standard\VarTagFactory;
use TypeLang\PHPDoc\Tag\Factory\TagFactory;

/**
 * Note: This driver requires installed "type-lang/phpdoc" and
 *       "type-lang/phpdoc-standard-tags" Composer packages.
 */
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

    private readonly ParamTagReader $paramTags;

    private readonly VarTagReader $varTags;

    /**
     * @var list<ClassConfigLoaderInterface>
     */
    private readonly array $classDocBlockLoaders;

    /**
     * @var list<PropertyConfigLoaderInterface>
     */
    private readonly array $propertyDocBlockLoaders;

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
        $docBlockParser = $this->createDocBlockParser($paramTagName, $varTagName);

        $this->varTags = new VarTagReader(
            varTagName: $varTagName,
            parser: $docBlockParser,
        );

        $this->paramTags = new ParamTagReader(
            paramTagName: $paramTagName,
            varTag: $this->varTags,
            parser: $docBlockParser,
        );

        $this->classDocBlockLoaders = $this->createClassLoaders();
        $this->propertyDocBlockLoaders = $this->createPropertyLoaders();

        parent::__construct($delegate);
    }

    /**
     * @param non-empty-string $paramTagName
     * @param non-empty-string $varTagName
     */
    private function createDocBlockParser(string $paramTagName, string $varTagName): DocBlockParser
    {
        self::assertKernelPackageIsInstalled();

        $typeParser = new Parser(tolerant: true);

        return new DocBlockParser(new TagFactory([
            $paramTagName => new ParamTagFactory($typeParser),
            $varTagName => new VarTagFactory($typeParser),
        ]));
    }

    /**
     * @return list<ClassDocBlockLoaderInterface>
     */
    private function createClassLoaders(): array
    {
        return [
        ];
    }

    /**
     * @return list<PropertyDocBlockLoaderInterface>
     */
    private function createPropertyLoaders(): array
    {
        return [
            new TypePropertyDocBlockLoader(
                paramTags: $this->paramTags,
                varTags: $this->varTags,
            ),
        ];
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private static function assertKernelPackageIsInstalled(): void
    {
        if (!\class_exists(DocBlockParser::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc',
                purpose: 'docblock support',
            );
        }

        if (!\class_exists(ParamTagFactory::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc-standard-tags',
                purpose: '"@param" tag support',
            );
        }

        if (!\class_exists(VarTagFactory::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc-standard-tags',
                purpose: '"@var" tag support',
            );
        }
    }

    #[\Override]
    protected function load(
        \ReflectionClass $reflection,
        ClassMetadata $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        foreach ($this->classDocBlockLoaders as $classDocBlockLoader) {
            $classDocBlockLoader->load(
                class: $reflection,
                metadata: $class,
                types: $types,
                parser: $parser,
            );
        }

        foreach ($reflection->getProperties() as $property) {
            $metadata = $class->getPropertyOrCreate($property->getName());

            foreach ($this->propertyDocBlockLoaders as $propertyDocBlockLoader) {
                $propertyDocBlockLoader->load(
                    property: $property,
                    metadata: $metadata,
                    types: $types,
                    parser: $parser,
                );
            }
        }

        $this->paramTags->cleanup();
    }
}
