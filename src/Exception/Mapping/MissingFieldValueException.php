<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class MissingFieldValueException extends RuntimeException implements
    FieldExceptionInterface,
    MappingExceptionInterface
{
    use FieldProvider;
    use TypeProvider;

    /**
     * @var int
     */
    public const CODE_ERROR_INVALID_VALUE = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_INVALID_VALUE;

    /**
     * @param non-empty-string $field
     */
    public function __construct(
        protected readonly TypeStatement $expected,
        protected readonly string $field,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @param non-empty-string $field
     */
    public static function createFromPath(
        TypeStatement $expected,
        string $field,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Object of type {{expected}} requires missing field {{field}}';

        return new self(
            expected: $expected,
            field: $field,
            path: $path,
            template: $template,
            code: self::CODE_ERROR_INVALID_VALUE,
            previous: $previous,
        );
    }

    /**
     * @param non-empty-string $field
     */
    public static function createFromContext(
        TypeStatement $expected,
        string $field,
        LocalContext $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            expected: $expected,
            field: $field,
            path: clone $context->getPath(),
            previous: $previous,
        );
    }
}
