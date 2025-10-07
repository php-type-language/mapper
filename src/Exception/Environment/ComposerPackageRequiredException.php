<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Environment;

/**
 * Occurs if any functionality requires the Composer package to be installed.
 */
class ComposerPackageRequiredException extends EnvironmentException
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $package,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($template, $code, $previous);
    }

    /**
     * @param non-empty-string $package
     * @param non-empty-string $purpose
     */
    public static function becausePackageNotInstalled(
        string $package,
        string $purpose,
        ?\Throwable $previous = null,
    ): self {
        $template = 'The {{package}} component is required to %s. '
            . 'Try running "composer require %s"';

        return new self(
            package: $package,
            template: \sprintf($template, $purpose, $package),
            previous: $previous,
        );
    }
}
