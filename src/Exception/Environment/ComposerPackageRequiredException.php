<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Environment;

final class ComposerPackageRequiredException extends EnvironmentException implements EnvironmentExceptionInterface
{
    /**
     * @param non-empty-string $package
     * @param non-empty-string $for
     */
    public static function becausePackageNotInstalled(string $package, string $for): self
    {
        $message = 'The "%s" component is required to %s. Try running "composer require %$1s"';

        return new self(\sprintf($message, $package, $for));
    }
}
