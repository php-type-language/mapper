<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

class JsonConfigReader extends ConfigFileReader
{
    /**
     * @var list<non-empty-string>
     */
    public const DEFAULT_YAML_FILE_EXTENSIONS = [
        '.json',
    ];

    public function __construct(
        iterable|string $directories,
        iterable|string $extensions = self::DEFAULT_YAML_FILE_EXTENSIONS,
        ReaderInterface $delegate = new ReflectionReader(),
    ) {
        parent::__construct($directories, $extensions, $delegate);
    }

    protected function load(\ReflectionClass $class): ?array
    {
        $pathname = $this->findPathname($class);

        if ($pathname === null) {
            return null;
        }

        $content = @\file_get_contents($pathname);

        if ($content === false) {
            throw new \RuntimeException(\sprintf(
                'Unable to read configuration file "%s"',
                $pathname,
            ));
        }

        return (array) \json_decode(
            json: $content,
            associative: true,
            flags: \JSON_THROW_ON_ERROR,
        );
    }
}
