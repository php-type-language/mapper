<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Mapper\Exception\Template;

abstract class RuntimeException extends \RuntimeException
{
    public readonly Template $template;

    public readonly PathInterface $path;

    public function __construct(
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($template, $code, $previous);

        $this->path = clone $path;

        /** @phpstan-ignore-next-line : Stringable is allowed to set in "message" */
        $this->message = $this->template = self::createTemplate(
            template: $template,
            context: $this,
            path: $path,
        );
    }

    public function updateMessage(string $message): void
    {
        $this->template->updateTemplateMessage($message);

        /** @phpstan-ignore-next-line : Message can be instance of Template */
        if ($this->message instanceof Template) {
            $this->message->updateTemplateMessage($message);
        }
    }

    private static function createTemplate(string $template, \Throwable $context, PathInterface $path): Template
    {
        $suffix = '';

        if (!$path->isEmpty()) {
            $suffix = ' at {{path}}';
        }

        return new Template(
            template: $template . $suffix,
            context: $context,
        );
    }
}
