<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Assert;

use Behat\Step\Then;
use PHPUnit\Framework\Assert;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Context\Provider\PlatformContext;
use TypeLang\Mapper\Tests\Context\Provider\TypeContext;
use TypeLang\Printer\PrettyPrinter;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class TypeStatementAssertions extends Context
{
    #[Then('/^the type must be defined$/')]
    public function thenTypeMustBeDefined(): void
    {
        $statement = $this->from(TypeContext::class)
            ->getStatement();

        $platform = $this->from(PlatformContext::class)
            ->getCurrent();

        foreach ($platform->getTypes() as $builder) {
            if ($builder->isSupported($statement)) {
                return;
            }
        }

        Assert::fail(\vsprintf('The type "%s" is not defined in "%s" platform', [
            (new PrettyPrinter())->print($statement),
            $platform->getName(),
        ]));
    }
}
