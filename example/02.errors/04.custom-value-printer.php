<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Mapper\Runtime\Path\PathPrinterInterface;

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
    // Before: "at $.values[1].values"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<ExampleDTO>,
    // but 42 given at $.values[1].values

    // Print full path using ">" delimiter
    $e->template->paths = new class implements PathPrinterInterface {
        public function print(PathInterface $path): string
        {
            return \implode(' > ', $path->toArray());
        }
    };

    // After: "at ExampleDTO > values > 1 > ExampleDTO > values"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<ExampleDTO>,
    // but 42 given at ExampleDTO > values > 1 > ExampleDTO > values
}
