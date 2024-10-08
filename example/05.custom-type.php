<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\DelegatePlatform;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Builder\NamedTypeBuilder;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

require __DIR__ . '/../vendor/autoload.php';

// You can also add your own types.

class MyNonEmptyStringType implements TypeInterface
{
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode('non-empty-string');
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return \is_string($value) && $value !== '';
    }

    public function cast(mixed $value, LocalContext $context): string
    {
        if (\is_string($value) && $value !== '') {
            return $value;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: 'non-empty-string',
            context: $context,
        );
    }
}


$mapper = new Mapper(new DelegatePlatform(
    delegate: new StandardPlatform(),
    types: [
        // Additional type
        new NamedTypeBuilder('non-empty-string', MyNonEmptyStringType::class)
    ],
));

$result = $mapper->normalize('example', 'non-empty-string');

var_dump($result);
//
// string(7) "example"
//

$result = $mapper->normalize('', 'non-empty-string');

var_dump($result);
//
// InvalidValueException: Passed value must be of type non-empty-string,
//                        but string given at root
//
