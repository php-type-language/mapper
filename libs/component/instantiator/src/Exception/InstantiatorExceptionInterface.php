<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Exception;

/**
 * Failure of creating an object of a class.
 *
 * @property-read string $class
 */
interface InstantiatorExceptionInterface extends \Throwable {}
