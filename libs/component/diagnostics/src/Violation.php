<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

use TypeLang\Mapper\Diagnostics\Path\Path;

/**
 * Single failure of mapping at a known location of the input.
 */
final class Violation
{
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
    ) {}

    /**
     * @param non-empty-string $template
     */
    public function withTemplate(string $template): self
    {
        return new self($this->code, $this->parameters, $this->path, $this->value, $template);
    }
}
