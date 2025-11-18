<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder\TypeAliasBuilder;

enum Reason
{
    /**
     * Non-canonical type alias.
     *
     * Used to denote type aliases that are specified in a non-canonical
     * form, such as `boolean` instead of `bool`
     */
    case NonCanonical;

    /**
     * Legacy and/or deprecated type alias.
     *
     * Marks type aliases that will be removed in the future.
     */
    case Deprecated;

    /**
     * Temporary type alias.
     *
     * Marks type aliases that may change semantics in future.
     */
    case Temporary;

    /**
     * A regular alias without any additional restrictions.
     */
    case Alternative;

    /**
     * @var self
     */
    public const DEFAULT = self::Alternative;
}
