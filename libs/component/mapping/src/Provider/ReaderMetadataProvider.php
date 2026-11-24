<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\DuplicateExternalNameException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidTypeException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\EnumMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\FieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;

/**
 * Provides the metadata the reader reads, built from its final draft and
 * checked: fields without a type, types left with a failure and fields of
 * one direction sharing a name, the name of the field or an alias, are
 * errors. Errors of the reader and of the checks are thrown together.
 */
final class ReaderMetadataProvider implements MetadataProviderInterface
{
    public function __construct(
        private readonly MetadataReaderInterface $reader,
    ) {}

    /**
     * @template TArgObject of object
     * @param \ReflectionClass<TArgObject> $class
     * @return ClassMetadata<TArgObject>
     * @throws InvalidMetadataException in case of the reader or the checks
     *         find wrong values
     * @throws ReaderException in case of the reader cannot be loaded
     */
    public function read(\ReflectionClass $class): ClassMetadata
    {
        $errors = [];

        try {
            $draft = $this->reader->read($class);
        } catch (InvalidMetadataException $e) {
            /** @var ClassDraft<TArgObject> */
            $draft = $e->metadata;
            $errors = $e->errors;
        }

        /** @var ClassMetadata<TArgObject> */
        $metadata = $draft->toMetadata();

        $errors = [
            ...$errors,
            ...self::getTypeErrors($class, $metadata),
            ...self::getNameErrors($class, $metadata->input->fields, 'denormalization'),
            ...self::getNameErrors($class, $metadata->output->fields, 'normalization'),
        ];

        if ($errors !== []) {
            throw InvalidMetadataException::becauseMetadataIsInvalid($draft, $errors);
        }

        return $metadata;
    }

    /**
     * @template TArg of \UnitEnum
     * @param \ReflectionEnum<TArg> $enum
     * @return EnumMetadata<TArg>
     * @throws InvalidMetadataException in case of the reader finds wrong
     *         values
     * @throws ReaderException in case of the reader cannot be loaded
     */
    public function readEnum(\ReflectionEnum $enum): EnumMetadata
    {
        /** @var EnumMetadata<TArg> */
        return $this->reader->read($enum)->toEnumMetadata();
    }

    /**
     * Returns the reference of the member the access reaches: `$id`,
     * `App\Base::$id` of a private property of a parent, `getId()`, the
     * class of an access of the user.
     *
     * @param \ReflectionClass<object> $class
     * @return non-empty-string
     */
    private static function formatMember(\ReflectionClass $class, AccessMetadata $access): string
    {
        return match (true) {
            ($access instanceof SlotAccessMetadata || $access instanceof HookAccessMetadata)
                && $access->class !== null
                && $access->class !== $class->name => $access->class . '::$' . $access->property,
            $access instanceof SlotAccessMetadata,
            $access instanceof HookAccessMetadata => '$' . $access->property,
            $access instanceof MethodAccessMetadata => $access->method . '()',
            default => $access::class,
        };
    }

    /**
     * Returns the reference of the member with its class: `App\User::$id`,
     * the class for an access of the user.
     *
     * @param \ReflectionClass<object> $class
     * @return non-empty-string
     */
    private static function formatTarget(\ReflectionClass $class, AccessMetadata $access): string
    {
        $member = self::formatMember($class, $access);

        return match (true) {
            \str_starts_with($member, '$'),
            $access instanceof MethodAccessMetadata => $class->name . '::' . $member,
            $access instanceof SlotAccessMetadata,
            $access instanceof HookAccessMetadata => $member,
            default => $class->name,
        };
    }

    /**
     * Returns the source of the declaration of the member, of the class for
     * an access of the user.
     *
     * @param \ReflectionClass<object> $class
     */
    private static function getMemberSource(\ReflectionClass $class, AccessMetadata $access): SourceInfo
    {
        if ($access instanceof MethodAccessMetadata && $class->hasMethod($access->method)) {
            return SourceInfo::createFromReflection($class->getMethod($access->method));
        }

        if ($access instanceof SlotAccessMetadata || $access instanceof HookAccessMetadata) {
            $owner = $access->class ?? $class->name;

            if (\property_exists($owner, $access->property)) {
                $property = new \ReflectionProperty($owner, $access->property);

                return SourceInfo::createFromReflection($property->getDeclaringClass());
            }
        }

        return SourceInfo::createFromReflection($class);
    }

    /**
     * Returns the fields without a type, the failures of the types of the
     * fields, one per member and failure, and of the discriminator.
     *
     * @param \ReflectionClass<object> $class
     * @param ClassMetadata<object> $metadata
     * @return list<InvalidTypeException>
     */
    private static function getTypeErrors(\ReflectionClass $class, ClassMetadata $metadata): array
    {
        $errors = $reported = [];

        foreach ([...$metadata->output->fields, ...$metadata->input->fields] as $field) {
            $type = $field->type;
            $target = self::formatTarget($class, $field->access);

            if ($type === null) {
                $source = self::getMemberSource($class, $field->access);
                $key = $target . "\0" . $source->findPlace();

                if (!isset($reported[$key])) {
                    $reported[$key] = true;
                    $errors[] = InvalidTypeException::becauseTypeIsNotDefined($target, $source);
                }

                continue;
            }

            if ($type->error === null) {
                continue;
            }

            $key = $target . "\0" . $type->error . "\0" . $type->source->findPlace();

            if (isset($reported[$key])) {
                continue;
            }

            $reported[$key] = true;
            $errors[] = InvalidTypeException::becauseTypeCannotBeRead(
                target: $target,
                reason: $type->error,
                source: $type->source,
            );
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

    /**
     * Returns the names given to more than one field of the direction or
     * more than once to one field.
     *
     * @param \ReflectionClass<object> $class
     * @param list<FieldMetadata> $fields
     * @param 'normalization'|'denormalization' $direction
     * @return list<DuplicateExternalNameException>
     */
    private static function getNameErrors(\ReflectionClass $class, array $fields, string $direction): array
    {
        $members = [];

        foreach ($fields as $field) {
            $names = [$field->name];

            if ($field instanceof InputFieldMetadata) {
                $names = [...$names, ...$field->aliases->names ?? []];
            }

            foreach ($names as $name) {
                $members[$name][] = self::formatMember($class, $field->access);
            }
        }

        $source = SourceInfo::createFromReflection($class);
        $errors = [];

        foreach ($members as $name => $references) {
            if (\count($references) < 2) {
                continue;
            }

            $unique = \array_values(\array_unique($references));

            if (\count($unique) === 1) {
                $errors[] = DuplicateExternalNameException::becauseFieldHasNameTwice(
                    member: $unique[0],
                    class: $class->name,
                    name: (string) $name,
                    source: $source,
                );

                continue;
            }

            $errors[] = DuplicateExternalNameException::becauseFieldsHaveSameName(
                members: $unique,
                class: $class->name,
                name: (string) $name,
                direction: $direction,
                source: $source,
            );
        }

        return $errors;
    }
}
