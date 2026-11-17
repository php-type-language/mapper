<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

/**
 * Config cannot be read or decoded or is not a map of classes.
 */
final class ConfigLoadingException extends ReaderException
{
    public static function becauseFileIsNotReadable(string $file): self
    {
        $message = \sprintf('File "%s" cannot be read', \addcslashes($file, '"'));

        return new self($message);
    }

    public static function becauseContentIsInvalid(string $file, \Throwable $previous): self
    {
        $message = \sprintf(
            'File "%s" cannot be decoded: %s',
            \addcslashes($file, '"'),
            $previous->getMessage(),
        );

        return new self($message, 0, $previous);
    }

    public static function becauseContentIsNotMap(string $file): self
    {
        $message = \sprintf('File "%s" must contain a map of classes', \addcslashes($file, '"'));

        return new self($message);
    }

    public static function becauseKeyIsNotClassName(int|string $key, ?string $file): self
    {
        $message = \sprintf(
            'Key "%s" of %s is not a class name',
            \addcslashes((string) $key, '"'),
            self::formatConfig($file),
        );

        return new self($message);
    }

    public static function becauseClassIsDescribedMoreThanOnce(string $class, ?string $file): self
    {
        $message = \sprintf(
            'Class "%s" of %s is described more than once',
            \addcslashes($class, '"'),
            self::formatConfig($file),
        );

        return new self($message);
    }

    private static function formatConfig(?string $file): string
    {
        if ($file === null) {
            return 'config';
        }

        return \sprintf('file "%s"', \addcslashes($file, '"'));
    }
}
