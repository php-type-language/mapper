<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Environment;

use TypeLang\Mapper\Exception\Template;

abstract class EnvironmentException extends \LogicException implements EnvironmentExceptionInterface
{
    public readonly Template $template;

    public function __construct(string $template, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($template, $code, $previous);

        $this->message = $this->template = new Template($template, $this);
    }
}
