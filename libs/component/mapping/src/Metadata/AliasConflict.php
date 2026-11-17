<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

/**
 * What a property takes on denormalization when the data has several of its
 * names: the external name and the aliases.
 */
enum AliasConflict: string
{
    /**
     * The second of the present names is a violation.
     *
     * ```
     * #[MapAlias(['mail', 'e_mail'], AliasConflict::Fail)]
     * public string $email;
     *
     * ['email' => 'a', 'mail' => 'b']   // violation at "mail"
     * ['e_mail' => 'c']                 // 'c' from "e_mail"
     * ```
     */
    case Fail = 'fail';

    /**
     * The value is taken from the present name that comes first in the data.
     *
     * ```
     * #[MapAlias(['mail', 'e_mail'], AliasConflict::UseFirst)]
     * public string $email;
     *
     * ['mail' => 'a', 'email' => 'b']   // 'a' from "mail"
     * ['email' => 'b', 'mail' => 'a']   // 'b' from "email"
     * ```
     */
    case UseFirst = 'first';

    /**
     * The value is taken from the present name that comes last in the data,
     * each next one overwrites the previous one.
     *
     * ```
     * #[MapAlias(['mail', 'e_mail'], AliasConflict::UseLast)]
     * public string $email;
     *
     * ['mail' => 'a', 'email' => 'b']   // 'b' from "email"
     * ['email' => 'b', 'mail' => 'a']   // 'a' from "mail"
     * ```
     */
    case UseLast = 'last';

    /**
     * The value is taken from the external name, in case of it is absent
     * from the alias that comes first in the data.
     *
     * ```
     * #[MapAlias(['mail', 'e_mail'], AliasConflict::UseNameOrFirst)]
     * public string $email;
     *
     * ['mail' => 'a', 'email' => 'b']   // 'b' from "email"
     * ['e_mail' => 'c', 'mail' => 'a']  // 'c' from "e_mail"
     * ```
     */
    case UseNameOrFirst = 'name_or_first';

    /**
     * The value is taken from the external name, in case of it is absent
     * from the alias that comes last in the data.
     *
     * ```
     * #[MapAlias(['mail', 'e_mail'], AliasConflict::UseNameOrLast)]
     * public string $email;
     *
     * ['email' => 'b', 'mail' => 'a']   // 'b' from "email"
     * ['e_mail' => 'c', 'mail' => 'a']  // 'a' from "mail"
     * ```
     */
    case UseNameOrLast = 'name_or_last';

    public const DEFAULT = self::Fail;
}
