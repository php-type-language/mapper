<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;

final class YamlConfigReader extends ConfigFileReader
{
    /**
     * @var list<non-empty-string>
     */
    public const DEFAULT_YAML_FILE_EXTENSIONS = [
        '.yml',
        '.yaml',
    ];

    private readonly YamlParser $parser;

    public function __construct(
        iterable|string $directories,
        iterable|string $extensions = self::DEFAULT_YAML_FILE_EXTENSIONS,
        ?YamlParser $parser = null,
        ReaderInterface $delegate = new ReflectionReader(),
    ) {
        parent::__construct($directories, $extensions, $delegate);

        $this->parser = $parser ?? $this->createDefaultYamlParser();
    }

    private function createDefaultYamlParser(): YamlParser
    {
        if (!\class_exists(YamlParser::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'symfony/yaml',
                purpose: 'YAML mapping configuration files',
            );
        }

        return new YamlParser();
    }

    protected function load(\ReflectionClass $class): ?array
    {
        $pathname = $this->findPathname($class);

        if ($pathname === null) {
            return null;
        }

        return (array) $this->parser->parseFile(
            filename: $pathname,
            flags: Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE,
        );
    }
}
