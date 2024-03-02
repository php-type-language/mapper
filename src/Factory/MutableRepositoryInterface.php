<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Factory;

/**
 * Provides a list of registered type factories and allows
 * to register a new type factory implementation.
 *
 * @template T of object
 *
 * @template-extends RepositoryInterface<T>
 */
interface MutableRepositoryInterface extends RepositoryInterface
{
    /**
     * Adds a new factory instance of type {@see T} to the repository.
     *
     * @param bool $append If {@see true}, the type factory will be added to
     *        the end of the registered factories list. In case of {@see false},
     *        it will be added to the beginning of the list.
     *
     * @param OptionalTypeFactoryInterface<T> $factory
     */
    public function add(OptionalTypeFactoryInterface $factory, bool $append = true): void;
}
