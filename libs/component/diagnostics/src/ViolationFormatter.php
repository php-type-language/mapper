<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

use TypeLang\Mapper\Diagnostics\Printer\Value\ValuePrinterInterface;
use TypeLang\Printer\TypePrinterInterface;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Renders the template of the violation, or of its code, with the
 * parameters, the expected type and the rejected value.
 *
 * ```
 * // list<App\Status>
 * (new ViolationFormatter())->format($violation);
 * // Value must be of type list<'new'|'paid'>, but "x" given
 *
 * (new ViolationFormatter(shouldPrintDeclaredType: true))->format($violation);
 * // Value must be of type list<App\Status>, but "x" given
 * ```
 */
final class ViolationFormatter implements ViolationFormatterInterface
{
    private const DEFAULT_REPLACEMENTS = [
        '{{expected}}' => '',
        '{{value}}' => '',
        '{{check}}' => '',
    ];

    public function __construct(
        private readonly ValuePrinterInterface $valuePrinter,
        /**
         * Prints the expected type, by default in a single line with all
         * fields of shapes
         */
        private readonly TypePrinterInterface $typePrinter,
        /**
         * Whether the declared type is printed in place of the expected one,
         * such as for a developer: with the classes of the application
         */
        private readonly bool $shouldPrintDeclaredType = true,
    ) {}

    /**
     * A code naming the expected type gives its untyped template in case of
     * the type is unknown. In a template of the user the unknown type and
     * the check of a violation of another kind are empty, such as
     * `{{check}}` of a value of a wrong type. Other unknown placeholders of
     * the template stay as they are.
     */
    public function format(Violation $violation): string
    {
        $replacements = self::DEFAULT_REPLACEMENTS;

        foreach ($violation->parameters as $name => $parameter) {
            $replacements['{{' . $name . '}}'] = (string) $parameter;
        }

        $type = $this->getType($violation);

        $replacements['{{expected}}'] = $this->typePrinter->print($type);
        $replacements['{{value}}'] = $this->valuePrinter->print($violation->value);

        return \strtr(self::getTemplate($violation, $type), $replacements);
    }

    private function getType(Violation $violation): TypeNode
    {
        if ($this->shouldPrintDeclaredType) {
            return $violation->declared
                ?? new NamedTypeNode(new Name('mixed'));
        }

        return $violation->expected
            ?? new NamedTypeNode(new Name('*hidden*'));
    }

    private static function getTemplate(Violation $violation, ?TypeNode $type): string
    {
        $code = $violation->code;

        if ($violation->template !== null) {
            return $violation->template;
        }

        if ($type === null && $code instanceof TypedViolationCodeInterface) {
            return $code->getUntypedTemplate();
        }

        return $code->getTemplate();
    }
}
