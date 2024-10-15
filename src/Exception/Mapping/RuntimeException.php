<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Exception\Template;
use TypeLang\Mapper\Runtime\Path\PathInterface;

abstract class RuntimeException extends \RuntimeException implements RuntimeExceptionInterface
{
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = 0x00;

    public readonly Template $template;

    protected readonly PathInterface $path;

    public function __construct(
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($template, $code, $previous);

        $suffix = '';

        if (!$path->isEmpty()) {
            $suffix = ' at {{path}}';
        }

        $this->path = clone $path;
        $this->message = $this->template = new Template(
            template: $template . $suffix,
            context: $this,
        );
    }

    /**
     * Returns the path to the field where the error occurred.
     *
     * @api
     */
    public function getPath(): PathInterface
    {
        return $this->path;
    }
}
