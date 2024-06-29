<?php

declare(strict_types=1);

use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Exception\Mapping\InvalidValueException;
use Serafim\Mapper\Mapper;
use Serafim\Mapper\Registry\Registry;
use Serafim\Mapper\Registry\RegistryInterface;
use Serafim\Mapper\Type\NonDirectionalType;

require __DIR__ . '/../vendor/autoload.php';

// You can also add your own types.

class MyNonEmptyStringType extends NonDirectionalType
{
    protected function format(mixed $value, RegistryInterface $types, LocalContext $context): string
    {
        if (!\is_string($value) || $value === '') {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: 'non-empty-string',
                actualValue: $value,
            );
        }

        return $value;
    }
}

// Create own types registry and add custom "non-empty-string" type.
$registry = new Registry();
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
