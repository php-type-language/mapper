<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\ContextInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class InvalidValueMappingException extends RuntimeException implements
    ValueExceptionInterface,
    MappingExceptionInterface
{
    use ValueProvider;
    use TypeProvider;

    /**
     * @var int
     */
    public const CODE_ERROR_INVALID_VALUE = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_INVALID_VALUE;

    public function __construct(
        protected readonly mixed $value,
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

    public static function createFromPath(
        mixed $value,
        TypeStatement $expected,
        PathInterface $path,
        ?\Throwable $prev = null
    ): self {
        $template = 'Passed value must be of type {{expected}}, but {{value}} given';

        return new self(
            value: $value,
            expected: $expected,
            path: $path,
            template: $template,
            code: self::CODE_ERROR_INVALID_VALUE,
            previous: $prev,
        );
    }

    public static function createFromContext(
        mixed $value,
        TypeStatement $expected,
        ContextInterface $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            value: $value,
            expected: $expected,
            path: clone $context->getPath(),
            prev: $previous,
        );
    }
}
