<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info;

use TypeLang\Mapper\Mapping\Info\ClassInfo\DiscriminatorInfo;
use TypeLang\Mapper\Mapping\Info\ClassInfo\PropertiesInfoList;

/**
 * @template T of object
 */
final class ClassInfo
{
    public readonly PropertiesInfoList $properties;

    public ?DiscriminatorInfo $discriminator = null;

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
        $this->properties = new PropertiesInfoList();
    }
}
