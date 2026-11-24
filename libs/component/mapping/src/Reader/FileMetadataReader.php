<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Reader\ReaderInterface;
use TypeLang\Reader\ReflectionReader;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given properties with a config stored in a file,
 * see {@see ArrayMetadataReader}.
 */
abstract class FileMetadataReader implements AccessSourceReaderInterface
{
    private ?ArrayMetadataReader $reader = null;

    public function __construct(
        /**
         * @var non-empty-string
         */
        protected readonly string $path,
        /**
         * Parser of the types of the config
         *
         * @var TypeParserInterface<TypeNode, string>
         */
        private readonly TypeParserInterface $parser = new TypeParser(),
        /**
         * Parser of the expressions of `skip`, created on the first one in
         * case of {@see null}
         */
        private readonly ?ExpressionLanguage $expressions = null,
        /**
         * Reader of the native types of the getters and the setters
         */
        private readonly ReaderInterface $nativeTypeReader = new ReflectionReader(),
    ) {}

    /**
     * @throws ConfigLoadingException in case of the file cannot be read or
     *         is not a map of classes
     * @throws InvalidMetadataException in case of the config of the class
     *         is wrong
     */
    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        return $this->getReader()->read($class, $metadata);
    }

    /**
     * @throws ConfigLoadingException in case of the file cannot be read or
     *         is not a map of classes
     * @throws InvalidMetadataException in case of an access key is wrong
     */
    public function readAccess(\ReflectionClass $class, ClassDraft $metadata): ClassDraft
    {
        return $this->getReader()->readAccess($class, $metadata);
    }

    /**
     * Decodes the content of the readable file, returns the config.
     *
     * @throws ConfigLoadingException in case of the content cannot be
     *         decoded
     */
    abstract protected function decode(): mixed;

    /**
     * Returns the reader of the config, the file is loaded on the first call.
     *
     * @throws ConfigLoadingException in case of the file cannot be read or
     *         decoded
     */
    private function getReader(): ArrayMetadataReader
    {
        return $this->reader ??= new ArrayMetadataReader(
            config: $this->load(),
            file: $this->path,
            parser: $this->parser,
            expressions: $this->expressions,
            nativeTypeReader: $this->nativeTypeReader,
        );
    }

    /**
     * @return array<array-key, mixed>
     * @throws ConfigLoadingException in case of the file cannot be read or
     *         decoded
     */
    private function load(): array
    {
        if (!\is_file($this->path) || !\is_readable($this->path)) {
            throw ConfigLoadingException::becauseFileIsNotReadable($this->path);
        }

        $config = $this->decode();

        if (!\is_array($config)) {
            throw ConfigLoadingException::becauseContentIsNotMap($this->path);
        }

        return $config;
    }
}
