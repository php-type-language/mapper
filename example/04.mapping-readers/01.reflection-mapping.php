<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        public readonly array $value = [],
    ) {}
}

// Create standard platform with REFLECTION READER
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    meta: new \TypeLang\Mapper\Mapping\Reader\ReflectionReader(),
);

$mapper = new Mapper($platform);

var_dump($mapper->denormalize([
    'value' => ['string', 'string 2'],
], ExampleDTO::class));

//
// Because NATIVE type hint is "array" that infers to "array<array-key, mixed>"
//
// object(ExampleDTO)#345 (1) {
//    ["value"] => array(2) {
//      [0] => string(6) "string"
//      [1] => string(8) "string 2"
//    }
//  }
//


