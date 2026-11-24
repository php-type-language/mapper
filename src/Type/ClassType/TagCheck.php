<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

/**
 * Tags of the class the data may have in the field of a discriminator on
 * denormalization, the field may be missing.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type
 */
final class TagCheck
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $field,
        /**
         * Tags of the class, a class taking the data with an unknown tag
         * may have none
         *
         * @var array<array-key, true>
         */
        public readonly array $tags,
        /**
         * Tags of the map of other classes, {@see null} in case of the class
         * takes the data with an unknown tag: only these tags are rejected
         *
         * @var array<array-key, true>|null
         */
        public readonly ?array $known = null,
    ) {}

    public function isAccepted(mixed $tag): bool
    {
        if (!\is_int($tag) && !\is_string($tag)) {
            return false;
        }

        if (isset($this->tags[$tag])) {
            return true;
        }

        return $this->known !== null && !isset($this->known[$tag]);
    }
}
