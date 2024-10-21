<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class MissingFieldValueException extends RuntimeException implements
    FieldExceptionInterface,
    MappingExceptionInterface
{
    use TypeProvider;
    use FieldProvider;

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
        protected readonly string $field,
        protected readonly TypeStatement $expected,
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
            field: $field,
            expected: $expected,
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
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            field: $field,
            expected: $expected,
            path: clone $context->getPath(),
            previous: $previous,
        );
    }
}
