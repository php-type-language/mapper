<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\FieldExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidFieldTypeValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueMappingException;
use TypeLang\Mapper\Exception\Mapping\MappingExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\MissingFieldTypeException;
use TypeLang\Mapper\Exception\Mapping\MissingFieldValueException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Runtime\ContextInterface;
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

    public function getTypeStatement(ContextInterface $context): TypeStatement
    {
        return $this->metadata->getTypeStatement($context);
    }

    protected function isNormalizable(mixed $value, ContextInterface $context): bool
    {
        $class = $this->metadata->getName();

        return $value instanceof $class;
    }

    /**
     * @return object|array<non-empty-string, mixed>
     * @throws InvalidValueException
     * @throws \Throwable
     */
    public function normalize(mixed $value, ContextInterface $context): object|array
    {
        $className = $this->metadata->getName();

        if (!$value instanceof $className) {
            throw InvalidValueMappingException::createFromContext(
                value: $value,
                expected: $this->metadata->getTypeStatement($context),
                context: $context,
            );
        }

        $entrance = $context->enter(new ObjectEntry($this->metadata->getName()));

        $result = $this->normalizeObject($value, $entrance);

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
    protected function normalizeObject(object $object, ContextInterface $context): array
    {
        $result = [];

        foreach ($this->metadata->getProperties() as $meta) {
            $entrance = $context->enter(new ObjectPropertyEntry($meta->getName()));

            // Skip the property when not readable
            if (!$this->accessor->isReadable($object, $meta)) {
                continue;
            }

            // Assert that type is present
            $info = $meta->findTypeInfo();
            if ($info === null) {
                throw MissingFieldTypeException::createFromContext(
                    field: $meta->getName(),
                    context: $entrance,
                );
            }

            $fieldValue = $this->accessor->getValue($object, $meta);

            // Skip the property when condition is matched
            $skip = $meta->findSkipCondition();
            if ($skip !== null) {
                $condition = $skip->getType();

                // Skip when condition is matched
                if ($condition->match($fieldValue, $entrance)) {
                    continue;
                }
            }

            $type = $info->getType();
            try {
                $result[$meta->getExportName()] = $type->cast($fieldValue, $entrance);
            } catch (FieldExceptionInterface|MappingExceptionInterface $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw InvalidFieldTypeValueException::createFromContext(
                    field: $meta->getExportName(),
                    value: $fieldValue,
                    expected: $info->getTypeStatement(),
                    object: $this->metadata->getTypeStatement($entrance),
                    context: $entrance,
                    previous: $e,
                );
            }
        }

        return $result;
    }

    protected function isDenormalizable(mixed $value, ContextInterface $context): bool
    {
        return \is_object($value) || \is_array($value);
    }

    /**
     * @return T
     * @throws InvalidValueException
     * @throws MissingFieldValueException
     * @throws \Throwable in case of object's property is not accessible
     */
    public function denormalize(mixed $value, ContextInterface $context): object
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueMappingException::createFromContext(
                value: $value,
                expected: $this->metadata->getTypeStatement($context),
                context: $context,
            );
        }

        $entrance = $context->enter(new ObjectEntry($this->metadata->getName()));

        $instance = $this->createInstance();

        $this->denormalizeObject($value, $instance, $entrance);

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
    private function denormalizeObject(array $value, object $object, ContextInterface $context): void
    {
        foreach ($this->metadata->getProperties() as $meta) {
            $entrance = $context->enter(new ObjectPropertyEntry($meta->getExportName()));

            // Skip the property when not writable
            if (!$this->accessor->isWritable($object, $meta)) {
                continue;
            }

            switch (true) {
                // In case of value has been passed
                case \array_key_exists($meta->getExportName(), $value):
                    // Assert that type is present
                    $info = $meta->findTypeInfo();

                    if ($info === null) {
                        throw MissingFieldTypeException::createFromContext(
                            field: $meta->getExportName(),
                            context: $entrance,
                        );
                    }

                    $fieldValue = $value[$meta->getExportName()];
                    $type = $info->getType();

                    try {
                        $propertyValue = $type->cast($fieldValue, $entrance);
                    } catch (FieldExceptionInterface|MappingExceptionInterface $e) {
                        throw $e;
                    } catch (\Throwable $e) {
                        throw InvalidFieldTypeValueException::createFromContext(
                            field: $meta->getExportName(),
                            value: $fieldValue,
                            expected: $info->getTypeStatement(),
                            object: $this->metadata->getTypeStatement($entrance),
                            context: $entrance,
                            previous: $e,
                        );
                    }
                    break;

                    // In case of property has default argument
                case $meta->hasDefaultValue():
                    $propertyValue = $meta->findDefaultValue();
                    break;

                default:
                    throw MissingFieldValueException::createFromContext(
                        expected: $this->getTypeStatement($entrance),
                        field: $meta->getExportName(),
                        context: $entrance,
                    );
            }

            $this->accessor->setValue($object, $meta, $propertyValue);
        }
    }
}
