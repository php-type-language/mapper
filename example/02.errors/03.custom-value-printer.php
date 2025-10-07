<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Runtime\Value\PHPValuePrinter;
use TypeLang\Mapper\Runtime\Value\SymfonyValuePrinter;

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
    var_dump($e->getMessage());

    // Print all values using PHP-compatible types
    $e->template->values = new PHPValuePrinter();

    // Before:  Passed value in "values" of {"values": 42} must be of type
    //          list<ExampleDTO>, but 42 given at $.values[1].values
    // After#1: Passed value in string of stdClass must be of type
    //          list<ExampleDTO>, but int given at $.values[1].values
    var_dump($e->getMessage());


    // In case of symfony/var-dumper is installed, we can use it
    if (\Composer\InstalledVersions::isInstalled('symfony/var-dumper')) {
        // Print all values using SymfonyValuePrinter
        $e->template->values = new SymfonyValuePrinter();

        // Before:  Passed value in "values" of {"values": 42} must be of type
        //          list<ExampleDTO>, but 42 given at $.values[1].values
        // After#1: Passed value in string of stdClass must be of type
        //          list<ExampleDTO>, but int given at $.values[1].values
        // After#2: Passed value in "values" of {#394
        //            +"values": 42
        //          } must be of type list<ExampleDTO>, but 42 given at $.values[1].values
        var_dump($e->getMessage());
    }
}
