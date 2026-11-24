<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Draft;

use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;

/**
 * One direction of a member: the name of its field, where the value is read
 * from or written to and its type there.
 */
final class AccessDraft
{
    public function __construct(
        public readonly AccessMetadata $access,
        /**
         * Name of the field in the data
         *
         * @var non-empty-string
         */
        public readonly string $name,
        /**
         * Whether the name is given by a reader, such as by an attribute,
         * {@see false} in case of it is the name of the member, renamed by
         * the naming strategy
         */
        public readonly bool $isNameExplicit = false,
        /**
         * Type of the value at the access, {@see null} in case of no reader
         * sets it
         */
        public readonly ?TypeMetadata $type = null,
    ) {}

    public function withType(TypeMetadata $type): self
    {
        return new self($this->access, $this->name, $this->isNameExplicit, $type);
    }

    /**
     * @param non-empty-string $name
     */
    public function withName(string $name, bool $isExplicit = true): self
    {
        return new self($this->access, $name, $isExplicit, $this->type);
    }
}
