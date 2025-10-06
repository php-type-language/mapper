<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

die('TODO: Not implemented yet');

class ExampleDTO
{
    public function __construct(
        /**
         * @var int<min, 0> This type will be ignored
         * @custom-var int<0, max> This type will be read
         */
        public readonly int $value = 0,
    ) {}
}

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    meta: new \TypeLang\Mapper\Mapping\Provider\DocBlockDriver(
        paramTagName: 'custom-param', // Override "@param" tag by the "@custom-param"
        varTagName: 'custom-var',     // Override "@var" tag by the "@custom-var"
        delegate: new \TypeLang\Mapper\Mapping\Provider\AttributeDriver(
            delegate: new \TypeLang\Mapper\Mapping\Provider\ReflectionDriver(),
        ),
    ),
);

$mapper = new Mapper($platform);

var_dump($mapper->normalize(new ExampleDTO(-42)));
//
// InvalidValueException: Passed value of field "value" must be of type
//                        int<0, max>, but -42 given at $.value
//
