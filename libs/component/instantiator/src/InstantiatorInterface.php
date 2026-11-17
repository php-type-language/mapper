<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator;

/**
 * Creates or finds the object of its class the data is written to, the
 * mapped properties are written after it. The way of creating is chosen
 * once by {@see InstantiatorFactory::createInstantiator()}.
 *
 * ```
 * $instantiator = $factory->createInstantiator($class, $metadata);
 *
 * $user = $instantiator->instantiate(['id' => 42, 'name' => 'John']);
 * ```
 *
 * @template-covariant T of object
 */
interface InstantiatorInterface
{
    /**
     * @param array<array-key, mixed> $data data the object is filled from,
     *        such as to find an existing object by its identifier
     * @return T
     */
    public function instantiate(array $data = []): object;
}
