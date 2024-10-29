<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Support;

use Behat\Step\Then;
use Symfony\Component\VarDumper\VarDumper;
use TypeLang\Mapper\Tests\Context\Context;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class VarDumperContext extends Context
{
    /**
     * @api
     */
    #[Then('/^dump "(?P<value>.+?)"$/')]
    public function thenDumpValue(mixed $value): void
    {
        VarDumper::dump($value);
    }
}
