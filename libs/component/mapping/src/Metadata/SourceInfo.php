<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

/**
 * Where a metadata value comes from.
 */
final class SourceInfo
{
    public function __construct(
        /**
         * File of the source, {@see null} in case of the source has none
         * (an array config, an internal class)
         *
         * @var non-empty-string|null
         */
        public readonly ?string $file = null,
        /**
         * Path to the value inside a config document
         *
         * @var non-empty-string|null
         */
        public readonly ?string $path = null,
    ) {}

    /**
     * Creates the source of the file declaring the class or the function, a
     * declaration without a file (an internal or an evaluated one) has none.
     *
     * @param \ReflectionClass<object>|\ReflectionFunctionAbstract $declaration
     */
    public static function createFromReflection(\ReflectionClass|\ReflectionFunctionAbstract $declaration): self
    {
        $file = $declaration->getFileName();

        if ($file === false || $file === '') {
            return new self();
        }

        return new self($file);
    }

    /**
     * Returns the place for a message: `file at path`, {@see null} in case of
     * the source has neither a file nor a path.
     *
     * @return non-empty-string|null
     */
    public function findPlace(): ?string
    {
        if ($this->file !== null && $this->path !== null) {
            return $this->file . ' at ' . $this->path;
        }

        return $this->file ?? $this->path;
    }
}
