<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Fields of one direction of a class share a name: a name or an alias.
 */
final class DuplicateExternalNameException extends MetadataException
{
    /**
     * @param non-empty-string $member member reference: `$id`, `getId()`
     */
    public static function becauseFieldHasNameTwice(
        string $member,
        string $class,
        string $name,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'field of "%s" has the name "%s" more than once among its name and aliases',
            \addcslashes($member, '"'),
            \addcslashes($name, '"'),
        );

        return new self($class, $source, $message);
    }

    /**
     * @param non-empty-list<non-empty-string> $members member references:
     *        `$id`, `getId()`
     * @param 'normalization'|'denormalization' $direction
     */
    public static function becauseFieldsHaveSameName(
        array $members,
        string $class,
        string $name,
        string $direction,
        SourceInfo $source,
    ): self {
        $message = \sprintf(
            'fields of "%s" have the same name "%s" in %s',
            \implode('", "', \array_map(
                static fn(string $member): string => \addcslashes($member, '"'),
                $members,
            )),
            \addcslashes($name, '"'),
            $direction,
        );

        return new self($class, $source, $message);
    }
}
