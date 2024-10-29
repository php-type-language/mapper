<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;

final class EnvironmentSet
{
    /**
     * @var \WeakMap<InitializedContextEnvironment, ContextSet>
     */
    private readonly \WeakMap $contexts;

    public function __construct()
    {
        $this->contexts = new \WeakMap();
    }

    public function get(InitializedContextEnvironment $env): ContextSet
    {
        return $this->contexts[$env] ??= ContextSet::fromEnvironment($env);
    }
}
