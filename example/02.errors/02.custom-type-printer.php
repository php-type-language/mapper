<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Exception\Mapping\RuntimeException;

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
} catch (RuntimeException $e) {
    // Before: "list<ExampleDTO>"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<ExampleDTO>,
    // but 42 given at $.values[1].values

    // Replace all "expected type" definition to PHP-supported printer
    // instead of PrettyPrinter
    $e->template->types = new \TypeLang\Printer\NativeTypePrinter();

    // After: "array"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type array,
    // but 42 given at $.values[1].values
}
