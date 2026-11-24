<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMetadata;
use TypeLang\Mapper\Type\Extractor\DiscriminatorTagExtractor;
use TypeLang\Mapper\Type\Extractor\LiteralTypeExtractor;

/**
 * Polymorphic class: the tag in the field of the data selects the type the
 * data is denormalized by. On normalization the type is selected by the
 * class of the object, the tag is added to the data in case of the type
 * does not write the field itself.
 *
 * ```
 * $type->denormalize(['type' => 'circle', 'radius' => 1], $context); // Circle
 * $type->normalize($circle, $context); // ['type' => 'circle', 'radius' => 1]
 * ```
 *
 * @template T of object
 *
 * @template-implements TypeInterface<T>
 */
final class DiscriminatorType implements TypeInterface
{
    public function __construct(
        /**
         * Description of the class of the type
         *
         * @var ClassMetadata<T>
         */
        public readonly ClassMetadata $metadata,
        /**
         * Description of the class with the discriminator: the class of the
         * type or its parent
         *
         * @var ClassMetadata<object>
         */
        public readonly ClassMetadata $owner,
        /**
         * Types by their tags, the classes of the map that are the class of
         * the type or its children
         *
         * @var array<array-key, TypeInterface<T>>
         */
        public readonly array $types,
        /**
         * Tags by the classes of the types, the types of other values are
         * not selected on normalization
         *
         * @var array<class-string, array-key>
         */
        public readonly array $tags,
        /**
         * Type of the data with no tag or with an unknown one, {@see null}
         * in case of such data is an error
         *
         * @var TypeInterface<T>|null
         */
        public readonly ?TypeInterface $default = null,
        /**
         * Type of an object without a tag, {@see null} in case of such an
         * object is an error
         *
         * @var TypeInterface<object>|null
         */
        public readonly ?TypeInterface $untagged = null,
        /**
         * Type of the class of the type itself, takes the data without a tag
         * instead of {@see $default}
         *
         * @var TypeInterface<T>|null
         */
        public readonly ?TypeInterface $own = null,
    ) {}

    /**
     * @throws TypeDefinitionException in case of no data creates the class
     */
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        $this->assertDenormalizable();

        $data = $context->coercers->array->coerce($value);

        if ($data === null) {
            return false;
        }

        $type = $this->findType($data);

        if ($type === null) {
            return false;
        }

        return $type->isDenormalizable($value, $context);
    }

    /**
     * @return T
     * @throws InvalidValueException in case of the value is not an array or
     *         an object, its tag is missing or unknown, or the selected type
     *         rejects it
     * @throws TypeDefinitionException in case of no data creates the class
     */
    public function denormalize(mixed $value, Context $context): object
    {
        $this->assertDenormalizable();

        $data = $context->coercers->array->coerce($value)
            ?? throw InvalidValueException::becauseInvalidType($value)
                ->withTemplate($this->getTypeErrorMessage());

        $type = $this->findType($data) ?? throw $this->createTagException($data);

        return $type->denormalize($value, $context);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        if (!$value instanceof $this->metadata->name) {
            return false;
        }

        $tag = $this->findTag($value);

        if ($tag !== null) {
            return $this->types[$tag]->isNormalizable($value, $context);
        }

        if ($this->untagged === null) {
            return false;
        }

        return $this->untagged->isNormalizable($value, $context);
    }

    /**
     * @throws InvalidValueException in case of the value is not an object of
     *         the class or has no tag, or cannot be normalized by its type
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        if (!$value instanceof $this->metadata->name) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        $tag = $this->findTag($value);

        if ($tag === null) {
            if ($this->untagged === null) {
                throw InvalidValueException::becauseObjectIsNotTagged($this->owner->name, $value);
            }

            return $this->untagged->normalize($value, $context);
        }

        $result = $this->types[$tag]->normalize($value, $context);
        $field = $this->getDiscriminator()->field;

        if ($result instanceof \stdClass && !\property_exists($result, $field)) {
            return (object) ([$field => $tag] + \get_object_vars($result));
        }

        if (!\is_array($result) || \array_key_exists($field, $result)) {
            return $result;
        }

        return [$field => $tag] + $result;
    }

    /**
     * Returns the type selected by the tag of the data: the default type in
     * case of the tag is unknown, the type of the class itself or the default
     * one in case of the tag is missing.
     *
     * @param array<array-key, mixed> $data
     * @return TypeInterface<T>|null
     */
    private function findType(array $data): ?TypeInterface
    {
        $field = $this->getDiscriminator()->field;

        if (!\array_key_exists($field, $data)) {
            return $this->own ?? $this->default;
        }

        $tag = $data[$field];

        if (\is_string($tag) || \is_int($tag)) {
            return $this->types[$tag] ?? $this->default;
        }

        return $this->default;
    }

    /**
     * Returns the tag of the class of the object or of its nearest mapped
     * parent, such as for a proxy of a mapped class.
     */
    private function findTag(object $object): int|string|null
    {
        return DiscriminatorTagExtractor::extract($this->tags, $object::class);
    }

    /**
     * Returns the error of the missing or unknown tag, with the path of the
     * field.
     *
     * @param array<array-key, mixed> $data
     */
    private function createTagException(array $data): InvalidValueException
    {
        $field = $this->getDiscriminator()->field;

        if (!\array_key_exists($field, $data)) {
            return InvalidValueException::becauseFieldIsMissing($field, new PropertySegment($field));
        }

        $declared = null;

        if ($this->types !== []) {
            $declared = LiteralTypeExtractor::extractAll(\array_keys($this->types));
        }

        return InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidType($data[$field], $declared),
            new PropertySegment($field),
            $data[$field],
        );
    }

    /**
     * Checks that some data creates the class: it or its child is in the
     * map, or the map has a type of the data with an unknown tag.
     *
     * @throws TypeDefinitionException in case of no data creates the class
     */
    private function assertDenormalizable(): void
    {
        if ($this->types === [] && $this->default === null) {
            throw TypeDefinitionException::becauseClassIsNotInDiscriminatorMap(
                $this->metadata->name,
                $this->owner->name,
            );
        }
    }

    /**
     * Returns the message of the class of the type or, in case of it has
     * none, of the class with the discriminator.
     *
     * @return non-empty-string|null
     */
    private function getTypeErrorMessage(): ?string
    {
        return $this->metadata->input->typeErrorMessage ?? $this->owner->input->typeErrorMessage;
    }

    private function getDiscriminator(): DiscriminatorMetadata
    {
        $discriminator = $this->owner->discriminator;

        \assert($discriminator !== null, 'The discriminator type is built for a class with a discriminator');

        return $discriminator;
    }
}
