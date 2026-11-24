<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Type\TypeNode;

/**
 * Single failure of mapping at a known location of the input.
 */
final class Violation
{
    /**
     * Whether the cause is an {@see \Error}: a defect of the code or of its
     * declarations, such as a call of a method that does not exist in a
     * setter, rather than of the input
     */
    public readonly bool $isFatal;

    public function __construct(
        public readonly ViolationCodeInterface $code,
        /**
         * Values of the template placeholders
         *
         * @var array<non-empty-string, string|\Stringable>
         */
        public readonly array $parameters,
        public readonly Path $path,
        /**
         * Rejected value, {@see null} for a missing field
         */
        public readonly mixed $value = null,
        /**
         * Template of the message with the placeholders of the parameters,
         * such as a text written by the user, the template of the code in
         * case of {@see null}
         *
         * @var non-empty-string|null
         */
        public readonly ?string $template = null,
        /**
         * Type the value at the path is declared with, such as the type of a
         * property, with the classes, enums and constants of the application.
         * {@see null} in case of it is unknown.
         */
        public readonly ?TypeNode $declared = null,
        /**
         * Type the value must be of for a reader outside of the application,
         * without the classes, enums and constants of the application.
         * {@see null} in case of it is unknown.
         */
        public readonly ?TypeNode $expected = null,
        /**
         * Error of another kind the value is rejected by, such as an error
         * of a hook of a class. Its message is not a part of the violation.
         */
        public readonly ?\Throwable $cause = null,
    ) {
        $this->isFatal = $cause instanceof \Error;
    }

    /**
     * @param non-empty-string $template
     */
    public function withTemplate(string $template): self
    {
        return new self(
            $this->code,
            $this->parameters,
            $this->path,
            $this->value,
            $template,
            $this->declared,
            $this->expected,
            $this->cause,
        );
    }

    public function withPath(Path $path): self
    {
        return new self(
            $this->code,
            $this->parameters,
            $path,
            $this->value,
            $this->template,
            $this->declared,
            $this->expected,
            $this->cause,
        );
    }

    public function withDeclared(?TypeNode $declared): self
    {
        return new self(
            $this->code,
            $this->parameters,
            $this->path,
            $this->value,
            $this->template,
            $declared,
            $this->expected,
            $this->cause,
        );
    }

    public function withExpected(?TypeNode $expected): self
    {
        return new self(
            $this->code,
            $this->parameters,
            $this->path,
            $this->value,
            $this->template,
            $this->declared,
            $expected,
            $this->cause,
        );
    }
}
