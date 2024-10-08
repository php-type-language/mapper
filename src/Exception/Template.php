<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

use TypeLang\Mapper\Path\JsonPathPrinter;
use TypeLang\Mapper\Path\PathInterface;
use TypeLang\Mapper\Path\PathPrinterInterface;
use TypeLang\Parser\Node\Statement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

/**
 * @internal this is an internal library class, please do not use it in your code.
 * @psalm-internal TypeLang\Mapper\Exception
 */
final class Template implements \Stringable
{
    public TypePrinterInterface $types;

    public PathPrinterInterface $paths;

    public function __construct(
        private readonly string $template,
        private readonly \Throwable $context,
    ) {
        $this->types = self::createDefaultTypePrinter();
        $this->paths = self::createDefaultPathPrinter();
    }

    private static function createDefaultTypePrinter(): TypePrinterInterface
    {
        return new PrettyPrinter(
            wrapUnionType: false,
            wrapIntersectionType: false,
            wrapCallableReturnType: false,
            multilineShape: \PHP_INT_MAX,
        );
    }

    private static function createDefaultPathPrinter(): PathPrinterInterface
    {
        return new JsonPathPrinter();
    }

    /**
     * @return iterable<non-empty-string, mixed>
     */
    private function getPlaceholders(): iterable
    {
        $reflection = new \ReflectionObject($this->context);

        foreach ($reflection->getProperties() as $property) {
            $placeholder = \sprintf('{{%s}}', $property->getName());

            if (!\str_contains($this->template, $placeholder)) {
                continue;
            }

            yield $placeholder => $property->getValue($this->context);
        }
    }

    private function formatValueToString(mixed $value): string
    {
        return match (true) {
            $value instanceof Statement => $this->types->print($value),
            $value instanceof PathInterface => $this->paths->print($value),
            $value === true => 'true',
            $value === false => 'false',
            \is_string($value) => $value,
            \is_scalar($value) => (string) $value,
            default => \get_debug_type($value),
        };
    }

    public function __toString(): string
    {
        $search = $replace = [];

        foreach ($this->getPlaceholders() as $placeholder => $value) {
            $search[] = $placeholder;
            $replace[] = $this->formatValueToString($value);
        }

        return \str_replace($search, $replace, $this->template);
    }
}
