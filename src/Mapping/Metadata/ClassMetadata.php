<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Represents an abstraction over general information about a class.
 *
 * @template T of object
 */
final class ClassMetadata extends Metadata
{
    public function __construct(
        /**
         * Gets full qualified class name.
         *
         * @var class-string<T>
         */
        public readonly string $name,
        /**
         * Contains a list of class fields available for normalization
         * and denormalization.
         *
         * @readonly
         * @psalm-readonly-allow-private-mutation
         * @var array<non-empty-string, PropertyMetadata>
         */
        public array $properties = [],
        /**
         * Gets {@see DiscriminatorMetadata} information about a class
         * discriminator map, or returns {@see null} if no such metadata
         * has been registered in the {@see ClassMetadata} instance.
         *
         * @readonly
         * @psalm-readonly-allow-private-mutation
         */
        public ?DiscriminatorMetadata $discriminator = null,
        /**
         * Gets information about the normalization method of an object.
         *
         * - Contains {@see true} if the object should be normalized as
         *   an associative {@see array}.
         *
         * - Contains {@see false} if the object should be normalized as an
         *   anonymous {@see object}.
         *
         * - Contains {@see null} if the system settings for this option
         *   should be used.
         */
        public readonly ?bool $isNormalizeAsArray = null,
        /**
         * An error message that occurs when an invalid type is processed.
         */
        public readonly ?string $typeErrorMessage = null,
        ?int $createdAt = null,
    ) {
        parent::__construct($createdAt);
    }

    /**
     * Dynamically creates AST class representation.
     *
     * Required to print type information in exceptions.
     *
     * @codeCoverageIgnore
     */
    public function getTypeStatement(Context $context, bool $read): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->name);
        }

        $fields = [];

        foreach ($this->properties as $property) {
            $fields[] = $property->getFieldNode($context, $read);
        }

        if ($fields === []) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode($this->name, fields: new FieldsListNode($fields));
    }
}
