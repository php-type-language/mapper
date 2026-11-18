<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

/**
 * The type cannot be expanded: an expander of the user failed or the type
 * of a name cannot be built.
 */
final class TypeExpansionException extends \RuntimeException implements MapperExceptionInterface
{
    public static function becauseTypeCannotBeExpanded(\Throwable $previous): self
    {
        return new self(
            message: \sprintf('Type cannot be expanded: %s', $previous->getMessage()),
            previous: $previous,
        );
    }
}
