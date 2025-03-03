<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type;

use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Runtime\Context;

interface TypeInterface
{
    /**
     * Checks that the value matches the selected type
     */
    public function match(mixed $value, Context $context): bool;

    /**
     * @throws RuntimeException in case of known mapping issue
     * @throws \Throwable in case of internal error occurs
     */
    public function cast(mixed $value, Context $context): mixed;
}
