<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use Behat\Step\Given;
use PHPUnit\Framework\Assert;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('type')]
final class TypeContext extends Context
{
    private ?TypeInterface $current = null;

    /**
     * @api
     */
    public function getCurrent(): TypeInterface
    {
        Assert::assertNotNull($this->current, 'Type is not set');

        return $this->current;
    }

    /**
     * @api
     */
    public function setCurrent(TypeInterface $type): TypeInterface
    {
        return $this->current = $type;
    }

    #[Given('/^type "(?P<type>[a-zA-Z0-9_\x80-\xff\\\\]+?)"$/')]
    public function givenType(string $type): void
    {
        $this->givenTypeWith($type);
    }

    #[Given('/^type "(?P<type>[a-zA-Z0-9_\x80-\xff\\\\]+?)" with (?P<args>.+?)$/')]
    public function givenTypeWith(string $type, string $args = '{}'): void
    {
        try {
            $arguments = \json_decode($args, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Assert::fail($e->getMessage() . ' in type arguments definition ' . $args);
        }

        // @phpstan-ignore-next-line
        $this->setCurrent(new $type(...$arguments));
    }
}
