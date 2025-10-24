<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

use TypeLang\Mapper\Context\Path\Printer\JsonPathPrinter;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Context\Path\Printer\PathPrinterInterface;
use TypeLang\Mapper\Runtime\Value\JsonLikeValuePrinter;
use TypeLang\Mapper\Runtime\Value\ValuePrinterInterface;
use TypeLang\Parser\Node\Statement;
use TypeLang\Parser\Node\Stmt\Shape\FieldNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

final class Template implements \Stringable
{
    public TypePrinterInterface $types;

    public PathPrinterInterface $paths;

    public ValuePrinterInterface $values;

    public function __construct(
        private string $template,
        private readonly \Throwable $context,
    ) {
        $this->types = self::createDefaultTypePrinter();
        $this->paths = self::createDefaultPathPrinter();
        $this->values = self::createDefaultValuePrinter();
    }

    public function updateTemplateMessage(string $message): void
    {
        $this->template = $message;
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

    private static function createDefaultValuePrinter(): ValuePrinterInterface
    {
        return new JsonLikeValuePrinter();
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
            $value instanceof TemplateArgumentNode => $this->types->print($value->value),
            $value instanceof FieldNode => $this->types->print($value->type),
            $value instanceof PathInterface => $this->paths->print($value),
            default => $this->values->print($value),
        };
    }

    public function __toString(): string
    {
        $search = $replace = [];

        foreach ($this->getPlaceholders() as $placeholder => $value) {
            $replacement = $this->formatValueToString($value);

            $search[] = \sprintf('"%s"', $placeholder);
            $replace[] = \sprintf('"%s"', \addcslashes($replacement, '"'));

            $search[] = $placeholder;
            $replace[] = $replacement;
        }

        return \str_replace($search, $replace, $this->template);
    }
}
