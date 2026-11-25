<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

/**
 * An optional package or PHP function required by the setup is not
 * available.
 */
final class MissingDependencyException extends ReaderException
{
    /**
     * @param non-empty-string $package
     * @param non-empty-string $purpose
     */
    public static function becausePackageIsNotInstalled(string $package, string $purpose): self
    {
        $message = \sprintf(
            '%s requires the "%s" package, run "composer require %s"',
            $purpose,
            \addcslashes($package, '"'),
            \addcslashes($package, '"'),
        );

        return new self($message);
    }

    /**
     * @param non-empty-string $function
     * @param non-empty-string $purpose
     */
    public static function becauseFunctionIsNotAvailable(string $function, string $purpose): self
    {
        $message = \sprintf(
            '%s requires the "%s()" function, check the PHP extensions and "disable_functions"',
            $purpose,
            \addcslashes($function, '"'),
        );

        return new self($message);
    }
}
