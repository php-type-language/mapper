<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\Repository;
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

    public function cast(mixed $value, RepositoryInterface $types, LocalContext $context): string
    {
        if (!\is_string($value) || $value === '') {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: 'non-empty-string',
                actualValue: $value,
            );
        }

        return $value;
    }
}

// Create own types registry and add custom "non-empty-string" type.
$registry = new Repository();
$registry->type('non-empty-string', MyNonEmptyStringType::class);


$mapper = new Mapper($registry);

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
