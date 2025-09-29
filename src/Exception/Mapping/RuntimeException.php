<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Exception\Template;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;

abstract class RuntimeException extends \RuntimeException
{
    public readonly Template $template;

    protected readonly PathInterface $path;

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

    /**
     * @template T of \Throwable
     *
     * @param T $e
     *
     * @return T
     */
    public static function tryAdopt(\Throwable $e, Context $context): \Throwable
    {
        try {
            $property = new \ReflectionProperty($e, 'message');
            $message = $property->getValue($e);

            if (!\is_string($message)) {
                return $e;
            }

            $property->setValue($e, (string) self::createTemplate(
                template: $message,
                context: $e,
                path: clone $context->getPath(),
            ));
        } catch (\Throwable) {
            return $e;
        }

        return $e;
    }

    public function getPath(): PathInterface
    {
        return $this->path;
    }
}
