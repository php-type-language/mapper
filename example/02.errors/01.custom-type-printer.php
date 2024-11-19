<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;

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
    // Before
    var_dump($e->getMessage());
    // - Type: "list<ExampleDTO>"
    // - Message: Passed value in "values" of {"values": 42} must be of type
    //   list<ExampleDTO>, but 42 given at $.values[1].values

    // Replace all "expected type" definition to PHP-supported printer
    // instead of PrettyPrinter
    $e->template->types = new \TypeLang\Printer\NativeTypePrinter();

    // After
    var_dump($e->getMessage());
    // - Type: "array"
    // - Message: Passed value in "values" of {"values": 42} must be of type
    //   array, but 42 given at $.values[1].values
}
