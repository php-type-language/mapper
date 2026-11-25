<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

use TypeLang\Mapper\Diagnostics\Printer\Value\ValuePrinterInterface;
use TypeLang\Printer\TypePrinterInterface;
use TypeLang\Type\TypeNode;

/**
 * Renders the template of the violation, or of its code, with the
 * parameters, the expected type and the rejected value.
 *
 * ```
 * // list<App\Status>
 * $formatter = new ViolationFormatter($values, $types);
 * $formatter->format($violation);
 * // Value must be of type list<App\Status>, but "x" given
 *
 * $formatter = new ViolationFormatter(
 *     valuePrinter: $values,
 *     typePrinter: $types,
 *     shouldPrintDeclaredType: false,
 * );
 * $formatter->format($violation);
 * // Value must be of type list<'new'|'paid'>, but "x" given
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
         * Printer of the expected type
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

        $type = $this->selectType($violation);

        if ($type !== null) {
            $replacements['{{expected}}'] = $this->typePrinter->print($type);
        }

        $replacements['{{value}}'] = $this->valuePrinter->print($violation->value);

        return \strtr(self::getTemplate($violation, $type), $replacements);
    }

    /**
     * Returns the type printed in place of `{{expected}}`, {@see null} in
     * case of the violation has no such type.
     */
    private function selectType(Violation $violation): ?TypeNode
    {
        if ($this->shouldPrintDeclaredType) {
            return $violation->declared;
        }

        return $violation->expected;
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
