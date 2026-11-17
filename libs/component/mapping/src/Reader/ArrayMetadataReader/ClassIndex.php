<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;

use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;

/**
 * Keys of a config by class names: case-insensitive, without the leading
 * `\`.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class ClassIndex
{
    /**
     * @var array<string, non-empty-string>
     */
    private readonly array $keys;

    /**
     * @param array<array-key, mixed> $config
     * @param non-empty-string|null $file
     * @throws ConfigLoadingException in case of the config is not a map of
     *         classes
     */
    public function __construct(array $config, ?string $file = null)
    {
        $keys = [];

        foreach ($config as $key => $_) {
            if (!\is_string($key) || $key === '') {
                throw ConfigLoadingException::becauseKeyIsNotClassName($key, $file);
            }

            $class = \strtolower(\ltrim($key, '\\'));

            if (isset($keys[$class])) {
                throw ConfigLoadingException::becauseClassIsDescribedMoreThanOnce($key, $file);
            }

            $keys[$class] = $key;
        }

        $this->keys = $keys;
    }

    /**
     * Returns the key of the class in the config.
     *
     * @param \ReflectionClass<object> $class
     * @return non-empty-string|null
     */
    public function findKey(\ReflectionClass $class): ?string
    {
        return $this->keys[\strtolower($class->name)] ?? null;
    }
}
