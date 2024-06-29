<?php

declare(strict_types=1);

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Registry\Registry;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\TypeInterface;

require __DIR__ . '/../vendor/autoload.php';

// You can also add your own types.

class MyNonEmpty implements TypeInterface
{
    public function __construct(
        #[TargetTemplateArgument]
        private readonly TypeInterface $type,
    ) {}

    private function assertValidNonEmpty(mixed $value, LocalContext $context): void
    {
        if (!empty($value)) {
            return;
        }

        throw InvalidValueException::becauseInvalidValue(
            context: $context,
            expectedType: 'non-empty',
            actualValue: $value,
        );
    }

    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        $this->assertValidNonEmpty($value, $context);

        return $this->type->normalize($value, $types, $context);
    }

    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        $this->assertValidNonEmpty($value, $context);

        return $this->type->denormalize($value, $types, $context);
    }
}

// Create own types registry and add "non-empty" type.
$registry = new Registry();
$registry->type('non-empty', MyNonEmpty::class);

$mapper = new Mapper($registry);

var_dump($mapper->normalize('example', 'non-empty'));
//
// MissingTemplateArgumentsException: Type "non-empty" expects 1 template
//                                    argument, but only 0 were passed
//

var_dump($mapper->normalize('example', 'non-empty<string>'));
//
// string(7) "example"
//

var_dump($mapper->normalize([1, 2, 3], 'non-empty<mixed>'));
//
// array:3 [
//  0 => 1
//  1 => 2
//  2 => 3
// ]
//

var_dump($mapper->normalize([], 'non-empty<string>'));
//
// InvalidValueException: Passed value must be of type non-empty, but
//                        array given at root.
//


var_dump($mapper->normalize(null, 'non-empty<string>'));
//
// InvalidValueException: Passed value must be of type non-empty, but
//                        null given at root.
//
