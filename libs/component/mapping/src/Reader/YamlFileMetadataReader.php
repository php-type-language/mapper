<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given properties with a config stored in a YAML
 * file, see {@see FileMetadataReader}. Requires the `symfony/yaml` package.
 */
final class YamlFileMetadataReader extends FileMetadataReader implements OptionalMetadataReaderInterface
{
    /**
     * @param non-empty-string $path
     * @param TypeParserInterface<TypeNode, string> $parser parser of the
     *        types of the config
     * @throws MissingDependencyException in case of the `symfony/yaml`
     *         package is not installed
     */
    public function __construct(string $path, TypeParserInterface $parser = new TypeParser())
    {
        if (!self::isAvailable()) {
            throw MissingDependencyException::becausePackageIsNotInstalled('symfony/yaml', 'YAML config');
        }

        parent::__construct($path, $parser);
    }

    public static function isAvailable(): bool
    {
        return \class_exists(Yaml::class);
    }

    protected function decode(): mixed
    {
        try {
            return Yaml::parseFile($this->path, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (ParseException $e) {
            throw ConfigLoadingException::becauseContentIsInvalid($this->path, $e);
        }
    }
}
