<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Exception\DuplicateExternalNameException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidTypeException;
use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;

/**
 * Checks the final metadata provided by the delegate: types left with a
 * failure and properties sharing a name of a field, an external name or an
 * alias, are errors. Errors of
 * the delegate and of the checks are thrown together.
 */
final class ValidatingMetadataProvider implements MetadataProviderInterface
{
    public function __construct(
        private readonly MetadataProviderInterface $delegate,
    ) {}

    /**
     * @template TArgObject of object
     * @param \ReflectionClass<TArgObject> $class
     * @return ClassMetadata<TArgObject>
     * @throws InvalidMetadataException in case of the delegate or the checks
     *         find wrong values
     * @throws ReaderException in case of the delegate cannot
     *         provide the metadata
     */
    public function read(\ReflectionClass $class): ClassMetadata
    {
        $errors = [];

        try {
            $metadata = $this->delegate->read($class);
        } catch (InvalidMetadataException $e) {
            /** @var ClassMetadata<TArgObject> */
            $metadata = $e->metadata;
            $errors = $e->errors;
        }

        $errors = [
            ...$errors,
            ...self::getTypeErrors($class, $metadata),
            ...self::getExternalNameErrors($class, $metadata),
        ];

        if ($errors !== []) {
            throw InvalidMetadataException::becauseMetadataIsInvalid($metadata, $errors);
        }

        return $metadata;
    }

    /**
     * @template TArgObject of object
     * @param \ReflectionClass<TArgObject> $class
     * @param ClassMetadata<TArgObject> $metadata
     * @return list<InvalidTypeException>
     */
    private static function getTypeErrors(\ReflectionClass $class, ClassMetadata $metadata): array
    {
        $errors = [];

        foreach ($metadata->properties as $property) {
            $types = [$property->read?->type];

            if (!self::isSameFailure($property->read?->type, $property->write?->type)) {
                $types[] = $property->write?->type;
            }

            foreach ($types as $type) {
                if ($type?->error === null) {
                    continue;
                }

                $errors[] = InvalidTypeException::becauseTypeCannotBeRead(
                    target: MetadataException::formatPropertyTarget($class->name, $property->name),
                    reason: $type->error,
                    source: $type->source,
                );
            }
        }

        $discriminator = $metadata->discriminator;

        if ($discriminator === null) {
            return $errors;
        }

        foreach ([...\array_values($discriminator->map), $discriminator->default] as $type) {
            if ($type?->error === null) {
                continue;
            }

            $errors[] = InvalidTypeException::becauseTypeCannotBeRead(
                target: $class->name,
                reason: $type->error,
                source: $type->source,
            );
        }

        return $errors;
    }

    private static function isSameFailure(?TypeMetadata $read, ?TypeMetadata $write): bool
    {
        return $read === $write
            || ($read?->error === $write?->error && $read?->source === $write?->source);
    }

    /**
     * @template TArgObject of object
     * @param \ReflectionClass<TArgObject> $class
     * @param ClassMetadata<TArgObject> $metadata
     * @return list<DuplicateExternalNameException>
     */
    private static function getExternalNameErrors(\ReflectionClass $class, ClassMetadata $metadata): array
    {
        $properties = [];

        foreach ($metadata->properties as $property) {
            foreach ([$property->externalName, ...$property->aliases] as $name) {
                $properties[$name][] = $property->name;
            }
        }

        $source = SourceInfo::createFromReflection($class);
        $errors = [];

        foreach ($properties as $name => $names) {
            if (\count($names) < 2) {
                continue;
            }

            $unique = \array_values(\array_unique($names));

            if (\count($unique) === 1) {
                $errors[] = DuplicateExternalNameException::becausePropertyHasNameTwice(
                    property: $unique[0],
                    class: $class->name,
                    name: (string) $name,
                    source: $source,
                );

                continue;
            }

            $errors[] = DuplicateExternalNameException::becausePropertiesHaveSameExternalName(
                properties: $unique,
                class: $class->name,
                name: (string) $name,
                source: $source,
            );
        }

        return $errors;
    }
}
