<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapProperty;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        public readonly int $value1 = 0,
        #[MapProperty('int<min, 0>')]
        public readonly int $value2 = 0,
    ) {}
}

// Create standard platform with attribute driver.
//
// Which means that we only read those fields that are marked with
// the "MapProperty" attribute.
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    driver: new \TypeLang\Mapper\Mapping\Driver\AttributeDriver(),
);

$mapper = new Mapper($platform);

$result = $mapper->denormalize(['value1' => 'asd', 'value2' => 23], ExampleDTO::class);

var_dump($result);
//
// InvalidFieldTypeValueException: Passed value of field "value2" must be of
//                                 type int<min, 0>, but 23 given at $.value2
//

