<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given properties with a config stored in a NEON
 * file, see {@see FileMetadataReader}. Requires the `nette/neon` package.
 */
final class NeonFileMetadataReader extends FileMetadataReader implements OptionalMetadataReaderInterface
{
    /**
     * @param non-empty-string $path
     * @param TypeParserInterface<TypeNode, string> $parser parser of the
     *        types of the config
     * @throws MissingDependencyException in case of the `nette/neon` package
     *         is not installed
     */
    public function __construct(string $path, TypeParserInterface $parser = new TypeParser())
    {
        if (!self::isAvailable()) {
            throw MissingDependencyException::becausePackageIsNotInstalled('nette/neon', 'NEON config');
        }

        parent::__construct($path, $parser);
    }

    public static function isAvailable(): bool
    {
        return \class_exists(Neon::class);
    }

    protected function decode(): mixed
    {
        try {
            return Neon::decodeFile($this->path);
        } catch (NeonException $e) {
            throw ConfigLoadingException::becauseContentIsInvalid($this->path, $e);
        }
    }
}
