<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Reader\ConfigReader\SchemaValidator;

/**
 * @phpstan-import-type ClassConfigType from SchemaValidator
 */
final class ArrayReader extends ConfigReader
{
    public function __construct(
        /**
         * @var array<class-string, ClassConfigType>
         */
        private readonly array $config = [],
        ReaderInterface $delegate = new NullReader(),
    ) {
        parent::__construct($delegate);
    }

    protected function load(\ReflectionClass $class): ?array
    {
        return $this->config[$class->name] ?? null;
    }
}
