<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub\Hierarchy;

class AuditedCreated extends Created
{
    public string $by = 'root';
}
