<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\AccessPoint\AccessPointInterface;

/**
 * One direction of a property: where the value is read from or written to
 * and its type at this place.
 */
final class AccessMetadata
{
    public function __construct(
        public readonly AccessPointInterface $point,
        /**
         * Type of the value at the point, {@see null} in case of no reader
         * sets it
         */
        public readonly ?TypeMetadata $type = null,
    ) {}

    public function withType(TypeMetadata $type): self
    {
        return new self($this->point, $type);
    }
}
