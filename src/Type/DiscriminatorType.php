<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMetadata;
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
         * Description of the base class, its discriminator is not null
         *
         * @var ClassMetadata<T>
         */
        public readonly ClassMetadata $metadata,
        /**
         * Types by their tags
         *
         * @var non-empty-array<array-key, TypeInterface<T>>
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
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
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
     */
    public function denormalize(mixed $value, Context $context): object
    {
        $data = $context->coercers->array->coerce($value)
            ?? throw InvalidValueException::becauseInvalidType($value)
                ->withTemplate($this->metadata->typeErrorMessage);

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

        if ($this->default === null) {
            return false;
        }

        return $this->default->isNormalizable($value, $context);
    }

    /**
     * @throws InvalidValueException in case of the value is not an object of
     *         a class of the map or cannot be normalized by its type
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        if (!$value instanceof $this->metadata->name) {
            throw InvalidValueException::becauseInvalidType($value)
                ->withTemplate($this->metadata->typeErrorMessage);
        }

        $tag = $this->findTag($value);

        if ($tag === null) {
            if ($this->default === null) {
                throw InvalidValueException::becauseInvalidType($value)
                    ->withTemplate($this->metadata->typeErrorMessage);
            }

            return $this->default->normalize($value, $context);
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
     * Returns the type selected by the tag of the data, the default type in
     * case of the tag is missing or unknown.
     *
     * @param array<array-key, mixed> $data
     * @return TypeInterface<T>|null
     */
    private function findType(array $data): ?TypeInterface
    {
        $tag = $data[$this->getDiscriminator()->field] ?? null;

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
        $tag = $this->tags[$object::class] ?? null;

        if ($tag !== null) {
            return $tag;
        }

        $nearest = null;

        foreach ($this->tags as $class => $candidate) {
            if ($object instanceof $class && ($nearest === null || \is_subclass_of($class, $nearest))) {
                $nearest = $class;
                $tag = $candidate;
            }
        }

        return $tag;
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

        $declared = LiteralTypeExtractor::extractAll(\array_keys($this->types));

        return InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidType($data[$field], $declared),
            new PropertySegment($field),
            $data[$field],
        );
    }

    private function getDiscriminator(): DiscriminatorMetadata
    {
        $discriminator = $this->metadata->discriminator;

        \assert($discriminator !== null, 'The discriminator type is built for a class with a discriminator');

        return $discriminator;
    }
}
