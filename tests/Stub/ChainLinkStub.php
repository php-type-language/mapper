<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

/**
 * Link of a chain of objects, may refer back to the links before it.
 */
final class ChainLinkStub
{
    public ?ChainLinkStub $next = null;

    /**
     * @var list<ChainLinkStub>
     */
    public array $children = [];

    public static function chain(int $length): self
    {
        $head = new self();
        $tail = $head;

        for ($i = 1; $i < $length; ++$i) {
            $tail = $tail->next = new self();
        }

        return $head;
    }
}
