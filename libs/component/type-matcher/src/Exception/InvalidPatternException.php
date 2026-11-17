<?php

declare(strict_types=1);

namespace TypeLang\Mapper\TypeMatcher\Exception;

/**
 * A form of a pattern is written wrong.
 */
final class InvalidPatternException extends \InvalidArgumentException implements
    TypeMatcherExceptionInterface
{
    public static function becausePatternIsEmpty(): self
    {
        return new self('Pattern must contain at least one form');
    }

    public static function becauseFormIsNotParsed(string $form, \Throwable $previous): self
    {
        $message = \sprintf(
            'Pattern form "%s" is not a valid type: %s',
            \addcslashes($form, '"'),
            $previous->getMessage(),
        );

        return new self($message, 0, $previous);
    }

    public static function becauseFormIsNotSimpleName(string $form): self
    {
        $message = \sprintf(
            'Pattern form "%s" must be a name with no namespace, '
                . 'optionally followed by template arguments',
            \addcslashes($form, '"'),
        );

        return new self($message);
    }

    public static function becauseFormHasFields(string $form): self
    {
        $message = \sprintf(
            'Pattern form "%s" must not contain shape fields',
            \addcslashes($form, '"'),
        );

        return new self($message);
    }

    /**
     * @param int<1, max> $position
     */
    public static function becauseArgumentIsNotCapture(string $form, int $position): self
    {
        $message = \sprintf(
            'Template argument #%d of pattern form "%s" must be a name '
                . 'with no namespace, arguments, fields or hint',
            $position,
            \addcslashes($form, '"'),
        );

        return new self($message);
    }

    public static function becauseCaptureIsDuplicated(string $form, string $capture): self
    {
        $message = \sprintf(
            'Pattern form "%s" contains argument "%s" more than once',
            \addcslashes($form, '"'),
            \addcslashes($capture, '"'),
        );

        return new self($message);
    }

    public static function becauseFormIsDuplicated(string $form): self
    {
        $message = \sprintf(
            'Pattern form "%s" repeats the name and the number of '
                . 'arguments of another form',
            \addcslashes($form, '"'),
        );

        return new self($message);
    }
}
