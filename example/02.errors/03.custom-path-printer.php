<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Runtime\Value\SimpleValuePrinter;

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
        ],
    ], ExampleDTO::class);
} catch (RuntimeException $e) {
    // Before: "but 42 given"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<ExampleDTO>,
    // but 42 given at $.values[1].values

    // Print all values using SimpleValuePrinter
    $e->template->values = new SimpleValuePrinter();

    // After: "but int given"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<ExampleDTO>,
    // but int given at $.values[1].values
}
