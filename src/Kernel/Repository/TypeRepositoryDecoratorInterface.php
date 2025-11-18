<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository;

/**
 * Defines a repository that supports embedding/decoration.
 *
 * @internal this is an internal library interface, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
interface TypeRepositoryDecoratorInterface extends TypeRepositoryInterface
{
    /**
     * @internal internal method for passing the root calling context
     */
    public function setTypeRepository(TypeRepositoryInterface $parent): void;
}
