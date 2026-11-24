<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests\Stub;

final class PromotedChildStub extends PromotedParentStub
{
    public function __construct(
        public string $name = 'child',
    ) {
        parent::__construct();
    }
}
