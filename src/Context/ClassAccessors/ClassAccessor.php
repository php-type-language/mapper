<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\ClassAccessors;

use TypeLang\Mapper\Hook\AfterDenormalizationHookInterface;
use TypeLang\Mapper\Hook\AfterNormalizationHookInterface;
use TypeLang\Mapper\Hook\BeforeDenormalizationHookInterface;
use TypeLang\Mapper\Hook\BeforeNormalizationHookInterface;
use TypeLang\Mapper\Instantiator\InstantiatorInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyReaderInterface;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;

/**
 * Everything a class is converted with: the instantiator of the class, the
 * readers and writers of its properties and its hooks, in the order they
 * run.
 *
 * @template T of object
 */
final class ClassAccessor
{
    public function __construct(
        /**
         * @var InstantiatorInterface<T>
         */
        public readonly InstantiatorInterface $instantiator,
        /**
         * Readers of the readable properties by PHP name
         *
         * @var array<non-empty-string, PropertyReaderInterface<T, mixed>>
         */
        public readonly array $readers,
        /**
         * Writers of the writable properties by PHP name
         *
         * @var array<non-empty-string, PropertyWriterInterface<T, mixed>>
         */
        public readonly array $writers,
        /**
         * @var list<BeforeDenormalizationHookInterface>
         */
        public readonly array $beforeDenormalization = [],
        /**
         * @var list<AfterDenormalizationHookInterface>
         */
        public readonly array $afterDenormalization = [],
        /**
         * @var list<BeforeNormalizationHookInterface>
         */
        public readonly array $beforeNormalization = [],
        /**
         * @var list<AfterNormalizationHookInterface>
         */
        public readonly array $afterNormalization = [],
    ) {}
}
