<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Nette\Neon\Neon as NeonParser;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;

final class NeonConfigReader extends ConfigFileReader
{
    /**
     * @var list<non-empty-string>
     */
    public const DEFAULT_YAML_FILE_EXTENSIONS = [
        '.neon',
    ];

    public function __construct(
        iterable|string $directories,
        iterable|string $extensions = self::DEFAULT_YAML_FILE_EXTENSIONS,
        ReaderInterface $delegate = new ReflectionReader(),
    ) {
        parent::__construct($directories, $extensions, $delegate);

        if (!\class_exists(NeonParser::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'nette/neon',
                purpose: 'NEON mapping configuration files',
            );
        }
    }

    protected function load(\ReflectionClass $class): ?array
    {
        $pathname = $this->findPathname($class);

        if ($pathname === null) {
            return null;
        }

        return (array) NeonParser::decodeFile($pathname);
    }
}
