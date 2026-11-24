<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Source;

/**
 * Config of one class or one property: its keys and the places of their
 * values.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class ConfigSection
{
    /**
     * @param non-empty-string $target class or property: `App\User`,
     *        `App\User::$id`
     * @param array<array-key, mixed> $values
     */
    private function __construct(
        public readonly string $target,
        private readonly array $values,
        private readonly ConfigPath $path,
        /**
         * Whether the config is one value written in place of the map, its
         * key has the place of the section
         */
        private readonly bool $isShorthand,
    ) {}

    /**
     * Creates the config written as a map: `id: {type: int}`.
     *
     * @param non-empty-string $target
     * @param array<array-key, mixed> $values
     * @param ConfigPath $path path of the section
     */
    public static function createFromMap(string $target, array $values, ConfigPath $path): self
    {
        return new self($target, $values, $path, false);
    }

    /**
     * Creates the config written as one value of the key: `id: int`.
     *
     * @param non-empty-string $target
     * @param non-empty-string $key
     * @param ConfigPath $path path of the section
     */
    public static function createFromValue(string $target, string $key, mixed $value, ConfigPath $path): self
    {
        return new self($target, [$key => $value], $path, true);
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->values);
    }

    /**
     * Returns the value of the key, {@see null} in case of there is no key.
     */
    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    /**
     * Returns the keys of the section.
     *
     * @return list<array-key>
     */
    public function getKeys(): array
    {
        return \array_keys($this->values);
    }

    /**
     * Returns the section with the given keys only.
     *
     * @param list<string> $keys
     */
    public function withOnly(array $keys): self
    {
        $values = \array_intersect_key($this->values, \array_flip($keys));

        return new self($this->target, $values, $this->path, $this->isShorthand);
    }

    /**
     * Returns the map of the key as a section of the same target,
     * {@see null} in case of the value is not a map.
     *
     * @param non-empty-string $key
     */
    public function findSection(string $key): ?self
    {
        $value = $this->get($key);

        if (!\is_array($value) || ($value !== [] && \array_is_list($value))) {
            return null;
        }

        $path = $this->path;

        if (!$this->isShorthand) {
            $path = $path->withKey($key);
        }

        return new self($this->target, $value, $path, false);
    }

    /**
     * Returns the place of the section itself.
     */
    public function createSectionContext(): SourceContext
    {
        return new SourceContext($this->target, $this->path->createSource());
    }

    /**
     * Returns the place of the value of the key, or of an item nested in that
     * value in case of the item keys are given: `aliases.on`,
     * `discriminator.map.circle`.
     *
     * @param non-empty-string $key
     */
    public function createContext(string $key, int|string ...$items): SourceContext
    {
        $path = $this->path;

        if (!$this->isShorthand) {
            $path = $path->withKey($key);
        }

        foreach ($items as $item) {
            $path = $path->withKey($item);
        }

        return new SourceContext($this->target, $path->createSource());
    }
}
