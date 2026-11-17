<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Account;

/**
 * @template T
 */
final class DocBlockStub
{
    /**
     * @var list<Account>
     */
    public array $accounts = [];

    /**
     * @var array
     * @phpstan-var non-empty-list<int>
     */
    public array $prefixed = [1];

    /**
     * @var int $other
     */
    public mixed $foreign = null;

    /**
     * @var T
     */
    public mixed $template = null;

    /**
     * @var self<T>|null
     */
    public ?object $self = null;
}
