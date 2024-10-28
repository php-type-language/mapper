<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

/**
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
interface InnerTypeRepositoryContainerInterface
{
    public function setInnerContext(TypeRepositoryInterface $inner): void;
}