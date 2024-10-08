<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Shape;

use TypeLang\Mapper\Exception\Definition\DefinitionException;

/**
 * An exception associated with ALL possible shape fields.
 */
abstract class ShapeFieldsException extends DefinitionException
{
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = parent::CODE_ERROR_LAST;
}
