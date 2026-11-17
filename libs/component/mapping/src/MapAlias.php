<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use TypeLang\Mapper\Mapping\Metadata\AliasConflict;

/**
 * Other names of the field of a property, or other values of the case of a
 * backed enum, accepted on denormalization. Normalization writes the name
 * of the property and the value of the case.
 *
 * ```
 * #[MapAlias('mail')]
 * #[MapAlias(['mail', 'e_mail'])]
 * public string $email;
 *
 * #[MapAlias(['active', 'on'])]
 * case Enabled = 'enabled';
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS_CONSTANT | \Attribute::IS_REPEATABLE)]
final class MapAlias
{
    /**
     * @var list<int|string>
     */
    public readonly array $aliases;

    /**
     * @param list<int|string>|int|string $aliases one alias or several of them
     */
    public function __construct(
        array|int|string $aliases,
        /**
         * What a property takes when the data has several of its names,
         * {@see AliasConflict::Fail} in case of {@see null}. Not allowed for
         * a case.
         */
        public readonly ?AliasConflict $onConflict = null,
    ) {
        if (!\is_array($aliases)) {
            $aliases = [$aliases];
        }

        $this->aliases = $aliases;
    }
}
