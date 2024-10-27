<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

abstract class TypeRepositoryDecorator implements
    TypeRepositoryInterface,
    InnerTypeRepositoryContainerInterface
{
    public function __construct(
        protected readonly TypeRepositoryInterface $delegate,
    ) {
        $this->setInnerContext($this);
    }

    public function setInnerContext(TypeRepositoryInterface $inner): void
    {
        if (!$this->delegate instanceof InnerTypeRepositoryContainerInterface) {
            return;
        }

        $this->delegate->setInnerContext($this);
    }
}
