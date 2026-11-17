<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class EnumDefaultStub
{
    public StatusStub $status = StatusStub::Active;

    /**
     * @param list<StatusStub> $statuses
     */
    public function __construct(
        public array $statuses = [StatusStub::Active, StatusStub::Inactive],
    ) {}
}
