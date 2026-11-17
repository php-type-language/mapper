<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

final class PromotedStub
{
    /**
     * @template TItem
     * @param list<UserStub> $users
     * @param int<0, max> $count
     * @param TItem $item
     */
    public function __construct(
        public string $required,
        public array $users = [],
        /**
         * @var positive-int
         */
        public int $count = 1,
        public ?int $nullable = null,
        public mixed $item = null,
    ) {}
}
