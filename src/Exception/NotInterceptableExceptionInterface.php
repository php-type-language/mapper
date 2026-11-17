<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

/**
 * Error passing through the composite types as is, without the path of
 * the value.
 */
interface NotInterceptableExceptionInterface extends \Throwable {}
