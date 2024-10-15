<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Parser\Node\Name;

interface AllowsExplainTypeInterface extends RuntimeExceptionInterface
{
    /**
     * @param \Closure(Name):(Name|null) $transform
     */
    public function explain(callable $transform): self;
}
