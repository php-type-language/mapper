<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Reader\ReaderInterface;
use TypeLang\Reader\ReflectionReader;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given properties with a config stored in a JSON
 * file, see {@see FileMetadataReader}. Requires the `json` PHP extension.
 */
final class JsonFileMetadataReader extends FileMetadataReader implements OptionalMetadataReaderInterface
{
    /**
     * @param non-empty-string $path
     * @param TypeParserInterface<TypeNode, string> $parser parser of the
     *        types of the config
     * @param ReaderInterface $nativeTypeReader reader of the native types of
     *        the getters and the setters
     * @throws MissingDependencyException in case of `json_decode()` is not
     *         available
     */
    public function __construct(
        string $path,
        TypeParserInterface $parser = new TypeParser(),
        ReaderInterface $nativeTypeReader = new ReflectionReader(),
    ) {
        if (!self::isAvailable()) {
            throw MissingDependencyException::becauseFunctionIsNotAvailable('json_decode', 'JSON config');
        }

        parent::__construct($path, $parser, $nativeTypeReader);
    }

    public static function isAvailable(): bool
    {
        return \function_exists('json_decode');
    }

    protected function decode(): mixed
    {
        $json = \file_get_contents($this->path);

        if ($json === false) {
            throw ConfigLoadingException::becauseFileIsNotReadable($this->path);
        }

        try {
            return \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ConfigLoadingException::becauseContentIsInvalid($this->path, $e);
        }
    }
}
