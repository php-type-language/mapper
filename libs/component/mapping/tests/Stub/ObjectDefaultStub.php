<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class ObjectDefaultStub
{
    /**
     * @param list<StatusStub|UserStub> $users
     */
    public function __construct(
        public UserStub $user = new UserStub(),
        public array $users = [StatusStub::Active, new UserStub()],
    ) {}
}
