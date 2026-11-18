<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class OrderStub
{
    /**
     * @var list<StatusStub>
     */
    public array $statuses = [];

    /**
     * @var list<array{level: LevelStub, suit: SuitStub}>
     */
    public array $items = [];

    public ?AddressStub $address = null;

    public ?\DateTimeImmutable $createdAt = null;
}
