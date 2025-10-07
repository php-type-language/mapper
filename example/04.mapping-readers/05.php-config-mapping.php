<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Runtime\Configuration;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        public readonly array $value = [],
    ) {}
}

// Create standard platform with PHP CONFIG READER
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    meta: new \TypeLang\Mapper\Mapping\Reader\PhpConfigReader(
        directories: __DIR__ . '/05.php-config-mapping',
    ),
);

$mapper = new Mapper($platform, new Configuration(
    isStrictTypes: false,
));

var_dump($mapper->denormalize([
    'value' => ['key' => 0, 1, 2],
], ExampleDTO::class));

//
// Because type in config file (05.php-config-mapping/ExampleDTO.php) is "list<bool>"
//
// object(ExampleDTO)#345 (1) {
//    ["value"] => array(2) {
//      [0] => bool(false)
//      [1] => bool(true)
//      [2] => bool(true)
//    }
//  }
//


