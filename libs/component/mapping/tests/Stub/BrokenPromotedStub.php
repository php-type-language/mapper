<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class BrokenPromotedStub
{
    /**
     * @param list<UserStub $users
     * @param positive-int $count
     */
    public function __construct(
        public array $users = [],
        public int $count = 1,
        /**
         * @var non-empty-string
         */
        public string $name = 'name',
        public ?int $nullable = null,
    ) {}
}
