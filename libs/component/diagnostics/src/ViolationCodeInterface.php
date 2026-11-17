<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

/**
 * Kind of a mapping violation. The mapper and its extensions declare their
 * kinds, usually as cases of an enum.
 */
interface ViolationCodeInterface
{
    /**
     * Returns the stable machine-readable code, such as `invalid_type`.
     *
     * @return non-empty-string
     */
    public function getCode(): string;

    /**
     * Returns the text with `{{name}}` placeholders of the parameters and
     * `{{value}}` of the rejected value.
     */
    public function getTemplate(): string;
}
