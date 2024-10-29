<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Support;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\When;
use TypeLang\Mapper\Tests\Context\Context;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class MarkersContext extends Context
{
    #[When('/^todo\h*(.*?)$/')]
    public function todo(string $message): void
    {
        $message = \trim($message);

        if ($message !== '') {
            throw new PendingException('TODO: ' . $message);
        }

        throw new PendingException('TODO');
    }
}
