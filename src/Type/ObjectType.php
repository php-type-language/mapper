<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\MissingFieldValueException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\ObjectType\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template T of object
 */
class ObjectType extends AsymmetricType
{
    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        private readonly ClassMetadata $metadata,
        private readonly PropertyAccessorInterface $accessor,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return $this->metadata->getTypeStatement($context);
    }

    protected function isNormalizable(mixed $value, LocalContext $context): bool
    {
        $class = $this->metadata->getName();

        return $value instanceof $class;
    }

    /**
     * @return object|array<non-empty-string, mixed>
     * @throws InvalidValueException
     * @throws \Throwable
     */
    public function normalize(mixed $value, LocalContext $context): object|array
    {
        $className = $this->metadata->getName();

        if (!$value instanceof $className) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        $context->enter(new ObjectEntry($this->metadata->getName()));

        $result = $this->normalizeObject($value, $context);

        $context->leave();

        if ($context->isObjectsAsArrays()) {
            return $result;
        }

        return (object) $result;
    }

    /**
     * @param T $object
     *
     * @return array<non-empty-string, mixed>
     * @throws \Throwable in case of object's property is not accessible
     */
    protected function normalizeObject(object $object, LocalContext $context): array
    {
        $result = [];

        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter(new ObjectPropertyEntry($meta->getName()));

            // Skip in case of property has no type definition.
            if (($type = $meta->findType()) === null) {
                continue;
            }

            $result[$meta->getExportName()] = $type->cast(
                value: $this->accessor->getValue($object, $meta),
                context: $context,
            );

            $context->leave();
        }

        return $result;
    }

    protected function isDenormalizable(mixed $value, LocalContext $context): bool
    {
        return \is_object($value) || \is_array($value);
    }

    /**
     * @return T
     * @throws InvalidValueException
     * @throws MissingFieldValueException
     * @throws \Throwable in case of object's property is not accessible
     */
    public function denormalize(mixed $value, LocalContext $context): object
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->metadata->getName(),
                context: $context,
            );
        }

        $context->enter(new ObjectEntry($this->metadata->getName()));

        $instance = $this->createInstance();

        $this->denormalizeObject($value, $instance, $context);

        $context->leave();

        return $instance;
    }

    /**
     * @return T
     * @throws \ReflectionException
     */
    private function createInstance(): object
    {
        /** @var \ReflectionClass<T> $reflection */
        $reflection = new \ReflectionClass($this->metadata->getName());

        return $reflection->newInstanceWithoutConstructor();
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @throws MissingFieldValueException
     * @throws \Throwable in case of object's property is not accessible
     */
    private function denormalizeObject(array $value, object $object, LocalContext $context): void
    {
        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter(new ObjectPropertyEntry($meta->getExportName()));

            switch (true) {
                // In case of value has been passed
                case \array_key_exists($meta->getExportName(), $value):
                    // Skip in case of property has no type definition.
                    if (($type = $meta->findType()) === null) {
                        continue 2;
                    }

                    $propertyValue = $type->cast($value[$meta->getExportName()], $context);
                    break;

                    // In case of property has default argument
                case $meta->hasDefaultValue():
                    $propertyValue = $meta->findDefaultValue();
                    break;

                default:
                    throw MissingFieldValueException::becausePropertyValueRequired(
                        field: $meta->getExportName(),
                        expected: $this->getTypeStatement($context),
                        context: $context,
                    );
            }

            $this->accessor->setValue($object, $meta, $propertyValue);

            $context->leave();
        }
    }
}
