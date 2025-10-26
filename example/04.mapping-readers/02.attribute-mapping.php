<?php

declare(strict_types=1);

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        #[MapType('list<string>')]
        public readonly array $value = [],
    ) {}
}

// Create standard platform with ATTRIBUTE READER
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    meta: new \TypeLang\Mapper\Mapping\Reader\AttributeReader(),
);

$mapper = new Mapper($platform, new Configuration(
    isStrictTypes: false,
));

var_dump($mapper->denormalize([
    'value' => ['key' => 1, 2, 3],
], ExampleDTO::class));

//
// Because type in attribute is "list<string>"
//
// object(ExampleDTO)#345 (1) {
//    ["value"] => array(2) {
//      [0] => string(1) "1"
//      [1] => string(1) "2"
//      [2] => string(1) "3"
//    }
//  }
//


