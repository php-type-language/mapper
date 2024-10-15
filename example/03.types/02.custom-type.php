<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\DelegatePlatform;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Mapper\Type\Builder\SimpleTypeBuilder;
use TypeLang\Mapper\Type\TypeInterface;

require __DIR__ . '/../../vendor/autoload.php';

// Add new type (must implement TypeInterface)
class MyNonEmptyStringType implements TypeInterface
{
    public function match(mixed $value, LocalContext $context): bool
    {
        return \is_string($value) && $value !== '';
    }

    public function cast(mixed $value, LocalContext $context): string
    {
        if (\is_string($value) && $value !== '') {
            return $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
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
// InvalidValueException: Passed value "" is invalid at $[1]
//
