<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class MissingValueException extends MappingException
{
    /**
     * @var int
     */
    public const CODE_ERROR_MISSING_PROPERTY_VALUE = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_MISSING_PROPERTY_VALUE;

    /**
     * @param TypeStatement|non-empty-string $expected
     */
    public static function becauseValueRequired(
        TypeStatement|string $expected,
        LocalContext $context,
        ?\Throwable $previous = null,
    ): self {
        $template = 'A value for type {{expected}} is required, but one has not been passed';

        $path = $context->getPath();

        if (!$path->isEmpty()) {
            $template .= ' at {{path}}';
        }

        return new self(
            expected: $expected,
            path: $path,
            template: $template,
            code: self::CODE_ERROR_MISSING_PROPERTY_VALUE,
            previous: $previous,
        );
    }
}
