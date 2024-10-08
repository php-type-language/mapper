<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Environment;

/**
 * Occurs if any functionality requires the Composer package to be installed.
 */
class ComposerPackageRequiredException extends EnvironmentException
{
    /**
     * @var int
     */
    public const CODE_ERROR_PACKAGE_NOT_INSTALLED = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_PACKAGE_NOT_INSTALLED;

    /**
     * @param non-empty-string $package
     */
    public function __construct(
        private readonly string $package,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($template, $code, $previous);
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

    /**
     * @param non-empty-string $package
     * @param non-empty-string $purpose
     */
    public static function becausePackageNotInstalled(
        string $package,
        string $purpose,
        ?\Throwable $previous = null,
    ): self {
        $template = 'The "{{package}}" component is required to %s. '
            . 'Try running "composer require {{package}}"';

        return new self(
            package: $package,
            template: \sprintf($template, $purpose),
            code: self::CODE_ERROR_PACKAGE_NOT_INSTALLED,
            previous: $previous,
        );
    }
}
