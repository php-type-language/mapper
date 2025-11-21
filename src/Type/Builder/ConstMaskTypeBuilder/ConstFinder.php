<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\ConstMaskTypeBuilder;

use TypeLang\Parser\Node\Identifier;
use TypeLang\Parser\Node\Name;

/**
 * Finds all constant values by the passed mask
 */
final class ConstFinder
{
    /**
     * @template TArgValue of mixed
     *
     * @param iterable<string, TArgValue> $constants
     *
     * @return list<TArgValue>
     */
    public function find(iterable $constants, Name $mask, ConstFinderMode $mode): array
    {
        $result = $mask->isSimple()
            ? $this->getSimpleConstantValues($constants, $mask->getLastPart(), $mode)
            : $this->getNamespacedConstantValues($constants, $mask, $mode);

        return \array_values(\array_unique($result));
    }

    /**
     * @template TArgValue of mixed
     *
     * @param iterable<string, TArgValue> $constants
     *
     * @return list<TArgValue>
     */
    private function getNamespacedConstantValues(iterable $constants, Name $name, ConstFinderMode $mode): array
    {
        $nameParts = $name->getParts();

        $expectedName = \array_pop($nameParts);
        $expectedNamespace = \strtolower(\implode('\\', $nameParts));

        $result = [];

        foreach ($constants as $constant => $value) {
            $delimiterOffset = \strrpos($constant, '\\');

            // Skip in case of constant name is not in namespace,
            // for example, "FOO" instead of "Bar\FOO"
            if ($delimiterOffset === false || $constant === '') {
                continue;
            }

            // Actual namespace prefix, for example, "Bar" from "Bar\FOO"
            $actualNamespace = \substr($constant, 0, $delimiterOffset);

            // PHP namespaces are case-insensitive, so we
            // need to compare them in lowercase
            if (\strtolower($actualNamespace) !== $expectedNamespace) {
                continue;
            }

            // Actual constant name, for example, "FOO" from "Bar\FOO"
            $actualName = \substr($constant, $delimiterOffset + 1);

            // Skip all inappropriate constants
            if ($actualName === '' || !$this->match($expectedName, $actualName, $mode)) {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * @template TArgValue of mixed
     *
     * @param iterable<string, TArgValue> $constants
     *
     * @return list<TArgValue>
     */
    private function getSimpleConstantValues(iterable $constants, Identifier $name, ConstFinderMode $mode): array
    {
        $result = [];

        foreach ($constants as $constant => $value) {
            // Skip all inappropriate constants
            if ($constant === '' || !$this->match($name, $constant, $mode)) {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param non-empty-string $actual
     */
    private function match(Identifier $expected, string $actual, ConstFinderMode $mode): bool
    {
        return match ($mode) {
            ConstFinderMode::Prefix => \str_starts_with($actual, $expected->value),
            ConstFinderMode::Suffix => \str_ends_with($actual, $expected->value),
            ConstFinderMode::Entrance => \str_contains($actual, $expected->value),
            default => $actual === $expected->value,
        };
    }
}
