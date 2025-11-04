<?php

declare(strict_types=1);

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        /**
         * @map-var list<bool>
         * @var list<string>
         */
        public readonly array $value = [],
    ) {}
}

// Create standard platform with PHPDOC READERS
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    meta: new \TypeLang\Mapper\Mapping\Reader\PhpDocReader(
        paramTagName: 'map-param',
        varTagName: 'map-var',
        returnTagName: 'map-return',
        // In the case of "@map-xxxx" annotations is missing,
        // then the @var/@param/@return will be used (fallback)
        delegate: new \TypeLang\Mapper\Mapping\Reader\PhpDocReader(
            varTagName: 'var',
        ),
    ),
);

$mapper = new Mapper($platform, new Configuration(
    strictTypes: false,
));

var_dump($mapper->denormalize([
    'value' => ['key' => 0, 1, 2],
], ExampleDTO::class));

//
// Because type in "@map-var" tag is "list<bool>".
//
// object(ExampleDTO)#345 (1) {
//    ["value"] => array(2) {
//      [0] => bool(false)
//      [1] => bool(true)
//      [2] => bool(true)
//    }
//  }
//


