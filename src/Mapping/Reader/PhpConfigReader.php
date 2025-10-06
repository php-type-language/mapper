<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

final class PhpConfigReader extends ConfigFileReader
{
    /**
     * @var list<non-empty-string>
     */
    public const DEFAULT_PHP_FILE_EXTENSIONS = [
        '.php',
    ];

    public function __construct(
        iterable|string $directories,
        iterable|string $extensions = self::DEFAULT_PHP_FILE_EXTENSIONS,
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

        \ob_start();

        try {
            $result = (require $pathname);
        } finally {
            $buffer = \ob_get_clean();
        }

        if ($buffer === '') {
            return (array) $result;
        }

        throw new \RuntimeException(\sprintf(
            'Invalid configuration file "%s" given',
            $pathname,
        ));
    }
}
