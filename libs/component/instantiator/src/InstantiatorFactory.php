<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator;

use TypeLang\Mapper\Instantiator\Exception\ClassNotInstantiableException;
use TypeLang\Mapper\Instantiator\Exception\InstantiatorExceptionInterface;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Instantiator\Strategy\WithoutConstructorStrategy;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Creates the instantiator of a class once by the first strategy that takes
 * the class, a class no strategy takes is rejected. The default strategy,
 * after the ones of the user, creates the objects without the constructor.
 *
 * ```
 * $factory = InstantiatorFactory::createDefault([new EntityStrategy($entities)]);
 *
 * $user = $factory->createInstantiator($class, $metadata)->instantiate(['id' => 42]);
 * ```
 */
final class InstantiatorFactory
{
    public function __construct(
        /**
         * @var non-empty-list<InstantiationStrategyInterface>
         */
        private readonly array $strategies,
    ) {}

    /**
     * @param list<InstantiationStrategyInterface> $strategies strategies of
     *        the user, tried before the default one in the order of the list
     */
    public static function createDefault(array $strategies = []): self
    {
        return new self([
            ...$strategies,
            new WithoutConstructorStrategy(),
        ]);
    }

    /**
     * @template TArgObject of object
     * @param \ReflectionClass<TArgObject> $class
     * @param ClassMetadata<TArgObject> $metadata
     * @return InstantiatorInterface<TArgObject>
     * @throws InstantiatorExceptionInterface in case of no strategy takes the
     *         class or the objects of the class cannot be created
     */
    public function createInstantiator(\ReflectionClass $class, ClassMetadata $metadata): InstantiatorInterface
    {
        foreach ($this->strategies as $strategy) {
            $instantiator = $strategy->tryCreateInstantiator($class, $metadata);

            if ($instantiator !== null) {
                return $instantiator;
            }
        }

        throw match (true) {
            $class->isInterface() => ClassNotInstantiableException::becauseClassIsInterface($class->name),
            $class->isTrait() => ClassNotInstantiableException::becauseClassIsTrait($class->name),
            $class->isEnum() => ClassNotInstantiableException::becauseClassIsEnum($class->name),
            default => ClassNotInstantiableException::becauseClassIsAbstract($class->name),
        };
    }
}
