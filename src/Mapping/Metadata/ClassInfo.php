<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

/**
 * @template T of object
 */
final class ClassInfo
{
    /**
     * @var array<non-empty-string, PropertyInfo>
     */
    public array $properties = [];

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
    ) {}

    /**
     * @param non-empty-string $name
     */
    public function getPropertyOrCreate(string $name): PropertyInfo
    {
        return $this->properties[$name] ??= new PropertyInfo($name);
    }
}
