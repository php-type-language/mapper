<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Value;

final class PrettyValuePrinter implements ValuePrinterInterface
{
    public const DEFAULT_MAX_ITEMS_COUNT = 3;

    public const DEFAULT_MAX_DEPTH = 1;

    public function __construct(
        private readonly int $maxItemsCount = self::DEFAULT_MAX_ITEMS_COUNT,
        private readonly int $maxDepth = self::DEFAULT_MAX_DEPTH,
    ) {}

    public function print(mixed $value, int $depth = 0): string
    {
        return match (true) {
            $value === true => 'true',
            $value === false => 'false',
            $value === null => 'null',
            \is_string($value) => \sprintf('"%s"', \addcslashes($value, '"')),
            \is_scalar($value),
            $value instanceof \Stringable => (string) $value,
            \is_array($value) => $this->printArray($value, $depth),
            \is_object($value) => $this->printObject($value, $depth),
            default => \get_debug_type($value),
        };
    }

    private function printObject(object $object, int $depth = 0): string
    {
        $name = 'object';

        if (!$object instanceof \stdClass) {
            $name .= '<' . $object::class . '>';
        }

        if ($depth >= $this->maxDepth) {
            return $name . '{...}';
        }

        $values = \get_object_vars($object);
        $result = $this->computeKeyValValues($values, $depth + 1);

        return \vsprintf('%s{%s%s}', [
            $name,
            \implode(', ', $result),
            $this->getArraySuffix(\count($values)),
        ]);
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return non-empty-string
     */
    private function printArray(array $values, int $depth): string
    {
        if (\array_is_list($values)) {
            return $this->printList($values, $depth);
        }

        if ($depth >= $this->maxDepth) {
            return \sprintf('array(%d){...}', \count($values));
        }

        $result = $this->computeKeyValValues($values, $depth + 1);

        return \vsprintf('array(%d){%s%s}', [
            $count = \count($values),
            \implode(', ', $result),
            $this->getArraySuffix($count),
        ]);
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return list<string>
     */
    private function computeKeyValValues(array $values, int $depth): array
    {
        $result = [];
        $index = 0;

        foreach ($values as $key => $value) {
            $result[] = \vsprintf('%s: %s', [
                $this->print($key, $depth),
                $this->print($value, $depth),
            ]);

            if (++$index >= $this->maxItemsCount) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param list<mixed> $values
     *
     * @return non-empty-string
     */
    private function printList(array $values, int $depth): string
    {
        if ($depth >= $this->maxDepth) {
            return \sprintf('array(%d)[...]', \count($values));
        }

        $result = $this->computeListValues($values, $depth + 1);

        return \vsprintf('array(%d)[%s%s]', [
            $count = \count($values),
            \implode(', ', $result),
            $this->getArraySuffix($count),
        ]);
    }

    /**
     * @param list<mixed> $values
     *
     * @return list<string>
     */
    private function computeListValues(array $values, int $depth): array
    {
        $result = [];
        $index = 0;

        foreach ($values as $value) {
            $result[] = $this->print($value, $depth);

            if (++$index >= $this->maxItemsCount) {
                break;
            }
        }

        return $result;
    }

    private function getArraySuffix(int $count): string
    {
        if ($count > $this->maxItemsCount) {
            return \sprintf(', ...+%d', $count - $this->maxItemsCount);
        }

        return '';
    }
}
