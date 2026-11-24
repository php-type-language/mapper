<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context\ClassAccessors\ClassAccessor;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Hook\AfterDenormalizationHookInterface;
use TypeLang\Mapper\Hook\AfterNormalizationHookInterface;
use TypeLang\Mapper\Hook\BeforeDenormalizationHookInterface;
use TypeLang\Mapper\Hook\BeforeNormalizationHookInterface;
use TypeLang\Mapper\Hook\ClassHookInterface;
use TypeLang\Mapper\Instantiator\Exception\InstantiatorExceptionInterface;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;

/**
 * Runtime side of the classes: turns the metadata of a class into its
 * instantiator, the readers and writers of its fields and its hooks by the
 * services of the mapper, once per metadata object, and keeps them while the
 * metadata exist. A reader or a writer has the position of its field in the
 * structure of the metadata.
 *
 * ```
 * $accessor = $accessors->get($metadata);
 *
 * $object = $accessor->instantiator->instantiate($data);
 * $accessor->writers[0]->write($object, 42);
 * ```
 */
final class ClassAccessors
{
    /**
     * @var \WeakMap<ClassMetadata<object>, ClassAccessor<object>>
     */
    private readonly \WeakMap $accessors;

    public function __construct(
        private readonly PropertyAccessorFactory $properties,
        private readonly InstantiatorFactory $instantiators,
        /**
         * Hooks of all classes, in the order they run
         *
         * @var list<ClassHookInterface>
         */
        private readonly array $hooks,
    ) {
        $this->accessors = new \WeakMap();
    }

    /**
     * @template TArgObject of object
     * @param ClassMetadata<TArgObject> $metadata
     * @return ClassAccessor<TArgObject>
     * @throws TypeDefinitionException in case of a strategy rejects the class
     *         or a property
     */
    public function get(ClassMetadata $metadata): ClassAccessor
    {
        /** @var ClassAccessor<TArgObject> */
        return $this->accessors[$metadata] ??= $this->create($metadata);
    }

    /**
     * @param ClassMetadata<object> $metadata
     * @return ClassAccessor<object>
     * @throws TypeDefinitionException in case of a strategy rejects the class
     *         or a property
     */
    private function create(ClassMetadata $metadata): ClassAccessor
    {
        try {
            $class = new \ReflectionClass($metadata->name);
            $instantiator = $this->instantiators->createInstantiator($class, $metadata);
            $readers = $writers = [];

            foreach ($metadata->output->fields as $field) {
                $readers[] = $this->properties->createReader($class, $field);
            }

            foreach ($metadata->input->fields as $field) {
                $writers[] = $this->properties->createWriter($class, $field);
            }
        } catch (PropertyAccessorExceptionInterface|InstantiatorExceptionInterface|\ReflectionException $e) {
            throw TypeDefinitionException::becauseClassIsNotAccessible($metadata->name, $e);
        }

        $before = $after = $beforeNormalization = $afterNormalization = [];

        foreach ($this->hooks as $hook) {
            if (!$hook->isSupported($metadata)) {
                continue;
            }

            if ($hook instanceof BeforeDenormalizationHookInterface) {
                $before[] = $hook;
            }

            if ($hook instanceof AfterDenormalizationHookInterface) {
                $after[] = $hook;
            }

            if ($hook instanceof BeforeNormalizationHookInterface) {
                $beforeNormalization[] = $hook;
            }

            if ($hook instanceof AfterNormalizationHookInterface) {
                $afterNormalization[] = $hook;
            }
        }

        return new ClassAccessor(
            instantiator: $instantiator,
            readers: $readers,
            writers: $writers,
            beforeDenormalization: $before,
            afterDenormalization: $after,
            beforeNormalization: $beforeNormalization,
            afterNormalization: $afterNormalization,
        );
    }
}
