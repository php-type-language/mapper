<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\MappingExceptionInterface;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Parser\Node\Name;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        #[MapType(type: 'list<ExampleDTO>')]
        public readonly array $values = [],
    ) {}
}

$mapper = new Mapper();

try {
    $result = $mapper->denormalize([
        'values' => [
            ['values' => []],
            ['values' => 42],
        ]
    ], ExampleDTO::class);
} catch (MappingExceptionInterface $e) {
    // Before: "list<ExampleDTO>"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<ExampleDTO>,
    // but 42 given at $.values[1].values

    // Replace all internal type names with common "object" keyword.
    $e = $e->explain(fn (Name $name): ?Name => \class_exists($name->toString())
        ? new Name('object')
        : null);

    // After: "list<object>"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<object>,
    // but 42 given at $.values[1].values
}
