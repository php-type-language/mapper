<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Environment;

final class ComposerPackageRequiredException extends EnvironmentException implements EnvironmentExceptionInterface
{
    /**
     * @param non-empty-string $package
     * @param non-empty-string $purpose
     */
    public function __construct(
        private readonly string $package,
        string $purpose,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $template = 'The {{package}} component is required to %s. Try running "composer require {{package}}"';

        parent::__construct(\sprintf($template, $purpose), $code, $previous);
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getPackage(): string
    {
        return $this->package;
    }
}
