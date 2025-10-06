<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

/**
 * @phpstan-type InExtensionsConfigType iterable<mixed, string>|string
 * @phpstan-type OutExtensionsConfigType list<string>
 *
 * @phpstan-type InDirectoriesConfigType iterable<mixed, non-empty-string>|non-empty-string
 * @phpstan-type OutDirectoriesConfigType list<non-empty-string>
 */
abstract class ConfigFileReader extends ConfigReader
{
    /**
     * @var OutDirectoriesConfigType
     */
    protected readonly array $directories;

    /**
     * @var OutExtensionsConfigType
     */
    protected readonly array $extensions;

    /**
     * @param InDirectoriesConfigType $directories
     * @param InExtensionsConfigType $extensions
     */
    public function __construct(
        iterable|string $directories,
        iterable|string $extensions = [],
        ReaderInterface $delegate = new ReflectionReader(),
    ) {
        $this->directories = $this->iterableOrStringToList($directories);
        $this->extensions = $this->iterableOrStringToList($extensions);

        parent::__construct($delegate);
    }

    /**
     * @template TArgValue of mixed
     *
     * @param iterable<mixed, TArgValue>|TArgValue $values
     * @return list<TArgValue>
     */
    private function iterableOrStringToList(mixed $values): array
    {
        return match (true) {
            $values instanceof \Traversable => \iterator_to_array($values, false),
            \is_array($values) => \array_values($values),
            default => [$values],
        };
    }

    /**
     * @return OutExtensionsConfigType
     */
    protected function getExpectedExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return OutDirectoriesConfigType
     */
    protected function getExpectedDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-list<non-empty-string>
     */
    protected function getExpectedFilenames(\ReflectionClass $class): array
    {
        $filename = \str_replace('\\', '.', $class->name);

        $result = [];

        foreach ($this->getExpectedExtensions() as $extension) {
            $result[] = $filename . $extension;
        }

        if (\count($result) === 0) {
            return [$filename];
        }

        return $result;
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return list<non-empty-string>
     */
    protected function getExpectedPathnames(\ReflectionClass $class): array
    {
        $result = [];

        foreach ($this->getExpectedDirectories() as $directory) {
            foreach ($this->getExpectedFilenames($class) as $filename) {
                $result[] = $directory . '/' . $filename;
            }
        }

        return $result;
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-string
     */
    protected function findPathname(\ReflectionClass $class): ?string
    {
        foreach ($this->getExpectedPathnames($class) as $pathname) {
            if (\is_file($pathname)) {
                return $pathname;
            }
        }

        return null;
    }
}
