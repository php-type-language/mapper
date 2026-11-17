<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;

/**
 * Overrides values of the given properties with a config returned by a PHP
 * file as an array, see {@see FileMetadataReader}.
 */
final class PhpFileMetadataReader extends FileMetadataReader
{
    protected function decode(): mixed
    {
        try {
            return self::requireFile($this->path);
        } catch (\Throwable $e) {
            throw ConfigLoadingException::becauseContentIsInvalid($this->path, $e);
        }
    }

    /**
     * @param non-empty-string $path
     */
    private static function requireFile(string $path): mixed
    {
        return require $path;
    }
}
