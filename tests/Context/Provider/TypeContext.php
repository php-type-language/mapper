<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider;

use Behat\Step\Given;
use PHPUnit\Framework\Assert;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('type')]
final class TypeContext extends Context
{
    private ?TypeInterface $current = null;

    private ?TypeStatement $statement = null;

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

    /**
     * @api
     */
    public function getStatement(): TypeStatement
    {
        Assert::assertNotNull($this->statement, 'Type statement is not set');

        return $this->statement;
    }

    /**
     * @api
     */
    public function setStatement(TypeStatement $type): TypeStatement
    {
        return $this->statement = $type;
    }

    #[Given('/^type statement "(?P<type>.+?)"$/')]
    public function givenTypeStatement(string $type): void
    {
        $parser = $this->from(TypeParserContext::class)
            ->getCurrent();

        $this->setStatement($parser->getStatementByDefinition($type));
    }

    #[Given('/^type "(?P<class>[a-zA-Z0-9_\x80-\xff\\\\]+?)"$/')]
    public function givenType(string $class): void
    {
        $this->givenTypeWith($class);
    }

    #[Given('/^type "(?P<class>[a-zA-Z0-9_\x80-\xff\\\\]+?)" with (?P<args>.+?)$/')]
    public function givenTypeWith(string $class, string $args = '{}'): void
    {
        try {
            $arguments = \json_decode($args, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Assert::fail($e->getMessage() . ' in type arguments definition ' . $args);
        }

        // @phpstan-ignore-next-line
        $this->setCurrent(new $class(...$arguments));
    }
}
