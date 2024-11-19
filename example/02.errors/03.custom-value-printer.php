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
    // Before
    var_dump($e->getMessage());
    // - Value#1: "of {"values": 42}"
    // - Value#2: "but 42 given"
    // - Message: Passed value in "values" of {"values": 42} must be of type
    //   list<ExampleDTO>, but 42 given at $.values[1].values

    // Print all values using PHP-compatible types
    $e->template->values = new PHPValuePrinter();

    // After#1
    var_dump($e->getMessage());
    // - Value#1: "of array"
    // - Value#2: "but int given"
    // - Message: Passed value in "values" of array must be of type
    //   list<ExampleDTO>, but int given at $.values[1].values


    // In case of symfony/var-dumper is installed, we can use it
    if (\Composer\InstalledVersions::isInstalled('symfony/var-dumper')) {
        // Print all values using SymfonyValuePrinter
        $e->template->values = new SymfonyValuePrinter();

        // After#2
        var_dump($e->getMessage());
        // - Value#1: "of array:1 [
        //     "values" => 42
        //   ]"
        // - Value#2: "but 42 given"
        // - Message: Passed value in "values" of array:1 [
        //     "values" => 42
        //   ] must be of type list<ExampleDTO>, but 42 given at $.values[1].values
    }
}
