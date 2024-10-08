<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template\Hint;

use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentException;

/**
 * An exception associated with ALL possible template argument hints.
 */
abstract class TemplateArgumentHintsException extends TemplateArgumentException
{
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = parent::CODE_ERROR_LAST;
}
