<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        public readonly int $value1 = 0,
        #[MapType('int<min, 0>')]
        public readonly int $value2 = 0,
    ) {}
}

// Create standard platform with attribute driver that extend the reflection
// driver.
//
// Which means that we read all public fields, as well as those marked
// with the MapProperty attribute with overwriting.
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    driver: new \TypeLang\Mapper\Mapping\Driver\AttributeDriver(
        delegate: new \TypeLang\Mapper\Mapping\Driver\ReflectionDriver(),
    ),
);

$mapper = new Mapper($platform);

$result = $mapper->denormalize(['value1' => 'asd', 'value2' => 23], ExampleDTO::class);

var_dump($result);
//
// InvalidFieldTypeValueException: Passed value of field "value1" must be of
//                                 type int, but "asd" given at $.value1
//

