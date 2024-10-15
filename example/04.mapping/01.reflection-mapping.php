<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    /**
     * @param int<min, 0> $value
     */
    public function __construct(
        public readonly int $value = 0,
    ) {}
}

// Create standard platform with reflection driver
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    driver: new \TypeLang\Mapper\Mapping\Driver\ReflectionDriver(),
);

$mapper = new Mapper($platform);

var_dump($mapper->normalize(new ExampleDTO(42)));
//
// array:1 [
//   "value" => 42
// ]
//

