<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Factory;

/**
 * Provides a list of registered type factories.
 *
 * @template-covariant T of object
 *
 * @template-extends \Traversable<array-key, OptionalTypeFactoryInterface<T>>
 */
interface RepositoryInterface extends \Traversable, \Countable {}
