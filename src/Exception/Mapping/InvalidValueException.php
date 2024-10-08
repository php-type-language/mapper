<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class InvalidValueException extends ValueMappingException
{
    /**
     * @var int
     */
    public const CODE_ERROR_INVALID_VALUE = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_INVALID_VALUE;

    /**
     * @param TypeStatement|non-empty-string $expected
     */
    public static function becauseInvalidValueGiven(
        mixed $value,
        TypeStatement|string $expected,
        LocalContext $context,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value must be of type {{expected}}, but {{actual}} given';

        if (\is_scalar($value)) {
            $template = \str_replace('{{actual}}', '{{actual}} ("{{value}}")', $template);
        }

        $path = $context->getPath();

        if (!$path->isEmpty()) {
            $template .= ' at {{path}}';
        }

        return new self(
            value: $value,
            expected: $expected,
            path: $path,
            template: $template,
            code: self::CODE_ERROR_INVALID_VALUE,
            previous: $previous,
        );
    }
}
