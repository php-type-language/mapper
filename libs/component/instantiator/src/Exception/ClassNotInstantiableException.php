<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Exception;

/**
 * The class exists, but an object of it cannot be created.
 */
final class ClassNotInstantiableException extends InstantiationException
{
    public static function becauseClassIsAbstract(string $class): self
    {
        $message = \sprintf('Cannot instantiate abstract class "%s"', \addcslashes($class, '"'));

        return new self($class, $message);
    }

    public static function becauseClassIsInterface(string $class): self
    {
        $message = \sprintf('Cannot instantiate interface "%s"', \addcslashes($class, '"'));

        return new self($class, $message);
    }

    public static function becauseClassIsTrait(string $class): self
    {
        $message = \sprintf('Cannot instantiate trait "%s"', \addcslashes($class, '"'));

        return new self($class, $message);
    }

    public static function becauseClassIsEnum(string $class): self
    {
        $message = \sprintf('Cannot instantiate enum "%s"', \addcslashes($class, '"'));

        return new self($class, $message);
    }

    public static function becauseNoStrategyTakesClass(string $class): self
    {
        $message = \sprintf('No instantiation strategy takes class "%s"', \addcslashes($class, '"'));

        return new self($class, $message);
    }

    public static function becauseClassRequiresConstructor(string $class, ?\Throwable $previous = null): self
    {
        $message = \sprintf(
            'Cannot instantiate class "%s" without the constructor',
            \addcslashes($class, '"'),
        );

        return new self($class, $message, $previous);
    }
}
