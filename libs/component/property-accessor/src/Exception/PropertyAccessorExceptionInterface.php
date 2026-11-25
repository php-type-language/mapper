<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Exception;

/**
 * The property cannot be read or written by any reader or writer.
 *
 * @property-read class-string $class
 */
interface PropertyAccessorExceptionInterface extends \Throwable {}
