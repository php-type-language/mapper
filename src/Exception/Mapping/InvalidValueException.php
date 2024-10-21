<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Context;

class InvalidValueException extends RuntimeException implements ValueExceptionInterface
{
    use ValueProvider;

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
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value {{value}} is invalid';

        return new self(
            value: $value,
            path: $path,
            template: $template,
            code: self::CODE_ERROR_INVALID_VALUE,
            previous: $previous,
        );
    }

    public static function createFromContext(
        mixed $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            value: $value,
            path: clone $context->getPath(),
            previous: $previous,
        );
    }
}
