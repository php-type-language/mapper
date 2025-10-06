<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorPrototype;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyPrototypeSet;

/**
 * @template T of object
 */
final class ClassPrototype
{
    public readonly PropertyPrototypeSet $properties;

    public ?DiscriminatorPrototype $discriminator = null;

    /**
     * @var non-empty-string|null
     */
    public ?string $typeErrorMessage = null;

    public ?bool $isNormalizeAsArray = null;

    public function __construct(
        /**
         * Gets full qualified class name.
         *
         * @var class-string<T>
         */
        public readonly string $name,
    ) {
        $this->properties = new PropertyPrototypeSet();
    }
}
