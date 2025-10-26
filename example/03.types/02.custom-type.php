<?php

declare(strict_types=1);

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\DelegatePlatform;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Builder\SimpleTypeBuilder;
use TypeLang\Mapper\Type\TypeInterface;

require __DIR__ . '/../../vendor/autoload.php';

// Add new type (must implement TypeInterface)
class MyNonEmptyStringType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value) && $value !== '';
    }

    public function cast(mixed $value, Context $context): string
    {
        if (\is_string($value) && $value !== '') {
            return $value;
        }

        throw new InvalidValueException(
            value: $value,
            path: $context->getPath(),
            template: 'Passed value cannot be empty, but {{value}} given',
        );
    }
}

$mapper = new Mapper(new DelegatePlatform(
    // Extend existing platform (StandardPlatform)
    delegate: new StandardPlatform(),
    types: [
        // Additional type
        new SimpleTypeBuilder('custom-string', MyNonEmptyStringType::class)
    ],
));

$result = $mapper->normalize(['example', ''], 'list<custom-string>');

var_dump($result);
//
// expected exception:
//   TypeLang\Mapper\Exception\Mapping\InvalidIterableValueException:
//     Passed value "" on index 1 in ["example", ""] is invalid at $[1]
//
// previous exception:
//   TypeLang\Mapper\Exception\Mapping\InvalidValueException:
//     Passed value cannot be empty, but "" given at $[1]
//
