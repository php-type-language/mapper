<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Group of errors related to incorrect number of template arguments
 */
abstract class TemplateArgumentsCountException extends TemplateArgumentsException
{
    /**
     * @param int<0, max> $passedArgumentsCount
     * @param int<0, max> $minSupportedArgumentsCount
     * @param int<0, max> $maxSupportedArgumentsCount
     */
    public function __construct(
        protected readonly int $passedArgumentsCount,
        protected readonly int $minSupportedArgumentsCount,
        protected readonly int $maxSupportedArgumentsCount,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($type, $template, $code, $previous);
    }

    /**
     * @api
     *
     * @return int<0, max>
     */
    public function getPassedArgumentsCount(): int
    {
        return $this->passedArgumentsCount;
    }

    /**
     * @api
     *
     * @return int<0, max>
     */
    public function getMinSupportedArgumentsCount(): int
    {
        return $this->minSupportedArgumentsCount;
    }

    /**
     * @api
     *
     * @return int<0, max>
     */
    public function getMaxSupportedArgumentsCount(): int
    {
        return $this->maxSupportedArgumentsCount;
    }
}
