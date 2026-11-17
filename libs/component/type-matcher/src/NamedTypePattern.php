<?php

declare(strict_types=1);

namespace TypeLang\Mapper\TypeMatcher;

use TypeLang\Mapper\TypeMatcher\Exception\InvalidPatternException;
use TypeLang\Parser\Exception\ParserExceptionInterface;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Template\TemplateNode;
use TypeLang\Type\Template\TypedTemplateArgumentNode;
use TypeLang\Type\TypeNode;

/**
 * A set of the simple forms a named type is allowed to be written in.
 *
 * Each form is a name with no namespace, followed by the names of its
 * template arguments, if it has any. A type matches a form when it has
 * the same name, in any case, the same number of arguments, no fields
 * and no argument hints. Names of the arguments are case sensitive.
 *
 * ```
 *  $pattern = new NamedTypePattern(['int', 'int<min, max>']);
 *
 *  $arguments = $pattern->findArguments($parser->parse('int<0, 42>'));
 *  $arguments['max']; // IntLiteralNode(42)
 * ```
 */
final class NamedTypePattern
{
    /**
     * Names of the arguments of each form, by the name in lower case and by
     * the number of arguments.
     *
     * @var non-empty-array<non-empty-lowercase-string, non-empty-array<int<0, max>, list<non-empty-string>>>
     */
    private readonly array $forms;

    /**
     * @param list<non-empty-string> $forms
     * @throws InvalidPatternException in case of a form is written wrong
     */
    public function __construct(
        array $forms,
        TypeParserInterface $parser = new TypeParser(),
    ) {
        if ($forms === []) {
            throw InvalidPatternException::becausePatternIsEmpty();
        }

        $index = [];

        foreach ($forms as $form) {
            $type = self::parseForm($parser, $form);
            $captures = self::readCaptures($form, $type);
            $name = $type->name->toLowerString();

            if (isset($index[$name][\count($captures)])) {
                throw InvalidPatternException::becauseFormIsDuplicated($form);
            }

            $index[$name][\count($captures)] = $captures;
        }

        $this->forms = $index;
    }

    /**
     * Returns the template arguments of the type by the names the matched
     * form gives them, empty for a form without arguments, {@see null} in
     * case of the type matches no form.
     *
     * @return array<non-empty-string, TypeNode>|null
     */
    public function findArguments(TypeNode $type): ?array
    {
        if (
            !$type instanceof NamedTypeNode
            || $type->fields !== null
            || $type->name->namespace !== null
        ) {
            return null;
        }

        $arguments = $type->arguments->children ?? [];
        $captures = $this->forms[$type->name->toLowerString()][\count($arguments)] ?? null;

        if ($captures === null) {
            return null;
        }

        $values = [];

        foreach ($arguments as $index => $argument) {
            if (!$argument instanceof TypedTemplateArgumentNode || $argument->variance !== null) {
                return null;
            }

            $values[$captures[$index]] = $argument->value;
        }

        return $values;
    }

    /**
     * @throws InvalidPatternException in case of the form is not a name
     *         with no namespace and no fields
     */
    private static function parseForm(TypeParserInterface $parser, string $form): NamedTypeNode
    {
        try {
            $type = $parser->parse($form);
        } catch (ParserExceptionInterface $e) {
            throw InvalidPatternException::becauseFormIsNotParsed($form, $e);
        }

        if (!$type instanceof NamedTypeNode || $type->name->namespace !== null) {
            throw InvalidPatternException::becauseFormIsNotSimpleName($form);
        }

        if ($type->fields !== null) {
            throw InvalidPatternException::becauseFormHasFields($form);
        }

        return $type;
    }

    /**
     * @return list<non-empty-string>
     * @throws InvalidPatternException in case of an argument is not a plain
     *         name or the name is repeated
     */
    private static function readCaptures(string $form, NamedTypeNode $type): array
    {
        $captures = [];

        foreach ($type->arguments->children ?? [] as $index => $argument) {
            $capture = self::findCapture($argument);

            if ($capture === null) {
                throw InvalidPatternException::becauseArgumentIsNotCapture($form, $index + 1);
            }

            if (\in_array($capture, $captures, true)) {
                throw InvalidPatternException::becauseCaptureIsDuplicated($form, $capture);
            }

            $captures[] = $capture;
        }

        return $captures;
    }

    /**
     * Returns {@see null} in case of the argument is anything but a name
     * with no namespace, arguments, fields and hint.
     *
     * @return non-empty-string|null
     */
    private static function findCapture(TemplateNode $argument): ?string
    {
        if (!$argument instanceof TypedTemplateArgumentNode || $argument->variance !== null) {
            return null;
        }

        $value = $argument->value;

        if (
            !$value instanceof NamedTypeNode
            || $value->name->namespace !== null
            || $value->arguments !== null
            || $value->fields !== null
        ) {
            return null;
        }

        return $value->name->value;
    }
}
