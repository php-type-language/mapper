<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../vendor/autoload.php';

// Each object by default supports attributes to describe the type,
// such as #[MapProperty] attribute.
//
// You can change this behavior by replacing the attributes with phpdoc
// annotations, changing the driver of the TypeLang\Mapper\Type\Builder\ObjectTypeBuilder,
// or specifying an argument to the TypeLang\Mapper\Platform\StandardPlatform
// which contains this object builder.

class ExampleDTO
{
    /**
     * @param int<min, 0> $value
     * @tl-param int<0, max> $value
     */
    public function __construct(
        public readonly int $value = 0,
    ) {}
}

$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    driver: new \TypeLang\Mapper\Mapping\Driver\DocBlockDriver(
        paramTagName: 'tl-param', // Read only "@tl-param" annotations for promoted properties
        varTagName: 'tl-var',     // Read only "@tl-var" annotations for basic properties
        delegate: new \TypeLang\Mapper\Mapping\Driver\AttributeDriver(),
    ),
);

$mapper = new Mapper($platform);

var_dump($mapper->normalize(new ExampleDTO(42)));
//
// All OK, since the type is described in the "@tl-param" annotation, which
// contains the type int<0, max>
//
// array:1 [
//   "value" => 42
// ]
//

var_dump($mapper->normalize(new ExampleDTO(-42)));
//
// InvalidValueException: Passed value must be of type int<0, max>, but
//                        int(-42) given in $.value
//
