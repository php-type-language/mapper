<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Alias of a value of a backed enum cannot be added.
 */
final class InvalidAliasException extends MetadataException
{
    /**
     * @param string $target case: `App\Status::Enabled`
     */
    public static function becauseConflictIsNotAllowed(string $target, SourceInfo $source): self
    {
        return new self($target, $source, 'alias conflict is allowed only for the aliases of a property');
    }

    /**
     * @param string $target class or case: `App\Status`, `App\Status::Enabled`
     */
    public static function becauseClassIsNotEnum(string $target, SourceInfo $source): self
    {
        return new self($target, $source, 'aliases are allowed only for the cases of an enum');
    }

    /**
     * @param string $target class or case: `App\Status`, `App\Status::Enabled`
     */
    public static function becauseAliasIsNotOfBackingType(
        int|string $alias,
        string $backing,
        string $target,
        SourceInfo $source,
    ): self {
        $message = \sprintf('alias %s must be of type "%s"', self::formatValue($alias), \addcslashes($backing, '"'));

        return new self($target, $source, $message);
    }

    /**
     * @param string $target class or case: `App\Status`, `App\Status::Enabled`
     */
    public static function becauseValueIsNotOfCase(int|string $value, string $target, SourceInfo $source): self
    {
        $message = \sprintf('value %s is not a value of a case', self::formatValue($value));

        return new self($target, $source, $message);
    }

    /**
     * @param string $target class or case: `App\Status`, `App\Status::Enabled`
     */
    public static function becauseAliasIsValueOfCase(int|string $alias, string $target, SourceInfo $source): self
    {
        $message = \sprintf('alias %s is already a value of a case', self::formatValue($alias));

        return new self($target, $source, $message);
    }

    /**
     * @param string $target class or case: `App\Status`, `App\Status::Enabled`
     */
    public static function becauseAliasIsDuplicated(int|string $alias, string $target, SourceInfo $source): self
    {
        $message = \sprintf('alias %s is given to more than one case', self::formatValue($alias));

        return new self($target, $source, $message);
    }

    private static function formatValue(int|string $value): string
    {
        if (\is_int($value)) {
            return (string) $value;
        }

        return '"' . \addcslashes($value, '"') . '"';
    }
}
