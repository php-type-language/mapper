<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Provider\TypeContext;

use Behat\Step\Given;
use TypeLang\Mapper\Tests\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Type\BackedEnumType;
use TypeLang\Mapper\Type\IntType;
use TypeLang\Mapper\Type\StringType;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class EnumTypeContext extends ExternalTypeContext
{
    #[Given('/^type "int-backed-enum"$/')]
    public function givenIntBackedEnumType(): void
    {
        $this->setCurrent(new BackedEnumType(
            class: IntBackedEnumStub::class,
            type: new IntType(),
        ));
    }

    #[Given('/^type "string-backed-enum"$/')]
    public function givenStringBackedEnumType(): void
    {
        $this->setCurrent(new BackedEnumType(
            class: StringBackedEnumStub::class,
            type: new StringType(),
        ));
    }
}
