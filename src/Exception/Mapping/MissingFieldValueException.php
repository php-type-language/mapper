<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Path\PathInterface;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class MissingFieldValueException extends MissingValueException
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
     * @param non-empty-string $field
     */
    public function __construct(
        protected readonly string $field,
        TypeStatement|string $expected,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            expected: $expected,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @param non-empty-string $field
     * @param TypeStatement|non-empty-string $expected
     */
    public static function becausePropertyValueRequired(
        string $field,
        TypeStatement|string $expected,
        LocalContext $context,
        ?\Throwable $previous = null,
    ): self {
        $template = 'An object of type {{expected}} requires a field {{field}} which must be passed at {{path}}';

        return new self(
            field: $field,
            expected: $expected,
            path: $context->getPath(),
            template: $template,
            code: self::CODE_ERROR_MISSING_PROPERTY_VALUE,
            previous: $previous,
        );
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
