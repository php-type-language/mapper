<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader\ClassPhpDocLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader\ParamConstructClassPhpDocLoader;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader\PropertyPhpDocLoaderInterface;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader\ReadHookTypePropertyPhpDocLoader;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader\TypePropertyPhpDocLoader;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader\WriteHookTypePropertyPhpDocLoader;
use TypeLang\Parser\Parser;
use TypeLang\PHPDoc\Parser as PhpDocParser;
use TypeLang\PHPDoc\Standard\ParamTagFactory;
use TypeLang\PHPDoc\Standard\ReturnTagFactory;
use TypeLang\PHPDoc\Standard\VarTagFactory;
use TypeLang\PHPDoc\Tag\Factory\TagFactory;

/**
 * @template-extends MetadataReader<ClassPhpDocLoaderInterface, PropertyPhpDocLoaderInterface>
 */
class PhpDocReader extends MetadataReader
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_PARAM_TAG_NAME = 'param';

    /**
     * @var non-empty-string
     */
    public const DEFAULT_VAR_TAG_NAME = 'var';

    /**
     * @var non-empty-string
     */
    public const DEFAULT_RETURN_TAG_NAME = 'return';

    private readonly PhpDocParser $parser;

    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $paramTagName = self::DEFAULT_PARAM_TAG_NAME,
        /**
         * @var non-empty-string
         */
        private readonly string $varTagName = self::DEFAULT_VAR_TAG_NAME,
        /**
         * @var non-empty-string
         */
        private readonly string $returnTagName = self::DEFAULT_RETURN_TAG_NAME,
        ReaderInterface $delegate = new ReflectionReader(),
    ) {
        $this->parser = $this->createPhpDocParser();

        parent::__construct($delegate);
    }

    #[\Override]
    protected function createClassLoaders(): array
    {
        return [
            new ParamConstructClassPhpDocLoader(
                paramTagName: $this->paramTagName,
                parser: $this->parser,
            ),
        ];
    }

    #[\Override]
    protected function createPropertyLoaders(): array
    {
        return [
            new TypePropertyPhpDocLoader(
                varTagName: $this->varTagName,
                parser: $this->parser,
            ),
            new ReadHookTypePropertyPhpDocLoader(
                returnTagName: $this->returnTagName,
                parser: $this->parser,
            ),
            new WriteHookTypePropertyPhpDocLoader(
                paramTagName: $this->paramTagName,
                parser: $this->parser,
            ),
        ];
    }

    private function createPhpDocParser(): PhpDocParser
    {
        self::assertKernelPackageIsInstalled();

        $typeParser = new Parser(tolerant: true);

        return new PhpDocParser(new TagFactory([
            $this->paramTagName => new ParamTagFactory($typeParser),
            $this->varTagName => new VarTagFactory($typeParser),
            $this->returnTagName => new ReturnTagFactory($typeParser),
        ]));
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private static function assertKernelPackageIsInstalled(): void
    {
        if (!\class_exists(PhpDocParser::class)) {
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

        if (!\class_exists(ReturnTagFactory::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'type-lang/phpdoc-standard-tags',
                purpose: '"@return" tag support',
            );
        }
    }
}
