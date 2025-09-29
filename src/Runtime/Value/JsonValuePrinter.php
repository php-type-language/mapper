<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Value;

final class JsonValuePrinter implements ValuePrinterInterface
{
    public const DEFAULT_MAX_ITEMS_COUNT = 3;

    public const DEFAULT_MAX_DEPTH = 1;

    public function __construct(
        private readonly int $maxItemsCount = self::DEFAULT_MAX_ITEMS_COUNT,
        private readonly int $maxDepth = self::DEFAULT_MAX_DEPTH,
    ) {}

    public function print(mixed $value): string
    {
        return $this->printMixed($value, 0);
    }

    /**
     * @param int<0, max> $depth
     *
     * @return non-empty-string
     */
    private function printMixed(mixed $value, int $depth): string
    {
        return match (true) {
            $value === true => $this->printTrue(),
            $value === false => $this->printFalse(),
            $value === null => $this->printNull(),
            \is_string($value) => $this->printString($value),
            \is_float($value) => $this->printFloat($value),
            \is_int($value) => $this->printInt($value),
            \is_resource($value) => $this->printResource($value),
            \is_array($value) => $this->printArray($value, $depth),
            $value instanceof \BackedEnum => $this->printBackedEnum($value, $depth),
            $value instanceof \UnitEnum => $this->printUnitEnum($value),
            \is_object($value) => $this->printObject($value, $depth),
            default => $this->printOther($value),
        };
    }

    /**
     * @return non-empty-string
     */
    private function printInt(int $value): string
    {
        return (string) $value;
    }

    /**
     * @return non-empty-string
     */
    private function printOther(mixed $value): string
    {
        /** @var non-empty-string */
        return \get_debug_type($value);
    }

    /**
     * @param resource $resource
     *
     * @return non-empty-string
     */
    private function printResource(mixed $resource): string
    {
        return (string) \get_resource_id($resource);
    }

    /**
     * @return non-empty-string
     */
    private function printTrue(): string
    {
        return 'true';
    }

    /**
     * @return non-empty-string
     */
    private function printFalse(): string
    {
        return 'false';
    }

    /**
     * @return non-empty-string
     */
    private function printNull(): string
    {
        return 'null';
    }

    /**
     * @return non-empty-string
     */
    private function printString(string $value): string
    {
        $formatted = \strtr($value, [
            "\n" => '\n',
            "\r" => '\r',
            "\t" => '\t',
        ]);

        return \sprintf('"%s"', \addcslashes($formatted, '"'));
    }

    /**
     * @return non-empty-string
     */
    private function printUnitEnum(\UnitEnum $case): string
    {
        return $this->printString($case->name);
    }

    /**
     * @param int<0, max> $depth
     *
     * @return non-empty-string
     */
    private function printBackedEnum(\BackedEnum $case, int $depth): string
    {
        return $this->printMixed($case->value, $depth);
    }

    /**
     * @return non-empty-string
     */
    private function printFloat(float $value): string
    {
        return match (true) {
            \is_nan($value) => 'NaN',
            \is_infinite($value) => $value > 0 ? 'Infinity' : '-Infinity',
            // In the case of float to an int cast without loss of precision
            // PHP converts such values to an int when converting to a string.
            //
            // Such cases should be handled separately.
            $value === (float) (int) $value => \number_format($value, 1, '.', ''),
            default => \sprintf('%g', $value),
        };
    }

    /**
     * @param int<0, max> $depth
     *
     * @return non-empty-string
     */
    private function printObject(object $object, int $depth): string
    {
        if ($object instanceof \Stringable) {
            $result = (string) $object;

            if ($result === '') {
                return '{}';
            }
        }

        if ($depth >= $this->maxDepth) {
            return '{...}';
        }

        $values = \get_object_vars($object);
        $result = $this->computeKeyValValues($values, $depth + 1);

        return \vsprintf('{%s%s}', [
            \implode(', ', $result),
            $this->getArraySuffix(\count($values)),
        ]);
    }

    /**
     * @param array<array-key, mixed> $values
     * @param int<0, max> $depth
     *
     * @return non-empty-string
     */
    private function printArray(array $values, int $depth): string
    {
        if (\array_is_list($values)) {
            return $this->printList($values, $depth);
        }

        if ($depth >= $this->maxDepth) {
            return '{...}';
        }

        $result = $this->computeKeyValValues($values, $depth + 1);

        return \vsprintf('{%s%s}', [
            \implode(', ', $result),
            $this->getArraySuffix(\count($values)),
        ]);
    }

    /**
     * @param array<array-key, mixed> $values
     * @param int<0, max> $depth
     *
     * @return list<string>
     */
    private function computeKeyValValues(array $values, int $depth): array
    {
        $result = [];
        $index = 0;

        foreach ($values as $key => $value) {
            $result[] = \vsprintf('%s: %s', [
                $this->printMixed($key, $depth),
                $this->printMixed($value, $depth),
            ]);

            if (++$index >= $this->maxItemsCount) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param list<mixed> $values
     * @param int<0, max> $depth
     *
     * @return non-empty-string
     */
    private function printList(array $values, int $depth): string
    {
        if ($depth >= $this->maxDepth) {
            return '[...]';
        }

        $result = $this->computeListValues($values, $depth + 1);

        return \vsprintf('[%s%s]', [
            \implode(', ', $result),
            $this->getArraySuffix(\count($values)),
        ]);
    }

    /**
     * @param list<mixed> $values
     * @param int<0, max> $depth
     *
     * @return list<string>
     */
    private function computeListValues(array $values, int $depth): array
    {
        $result = [];
        $index = 0;

        foreach ($values as $value) {
            $result[] = $this->printMixed($value, $depth);

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
