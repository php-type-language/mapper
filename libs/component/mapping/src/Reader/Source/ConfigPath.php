<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Source;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Place in a config of a file, the path is printed as JSONPath:
 * `$['App\User'].properties.id.type`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class ConfigPath
{
    /**
     * @param non-empty-string $path
     * @param non-empty-string|null $file
     */
    private function __construct(
        private readonly string $path,
        private readonly ?string $file,
    ) {}

    /**
     * Returns the path of the config itself: `$`.
     *
     * @param non-empty-string|null $file file of the config, {@see null} in
     *        case of the config has none
     */
    public static function createRoot(?string $file = null): self
    {
        return new self('$', $file);
    }

    /**
     * Returns the path of the child key.
     */
    public function withKey(int|string $key): self
    {
        if (\is_int($key)) {
            return new self($this->path . '[' . $key . ']', $this->file);
        }

        if (\preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $key) === 1) {
            return new self($this->path . '.' . $key, $this->file);
        }

        return new self($this->path . '[\'' . \addcslashes($key, '\'') . '\']', $this->file);
    }

    /**
     * Returns the source of the values of this place.
     */
    public function createSource(): SourceInfo
    {
        return new SourceInfo($this->file, $this->path);
    }
}
