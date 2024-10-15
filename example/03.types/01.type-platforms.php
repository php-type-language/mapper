<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\EmptyPlatform;

require __DIR__ . '/../../vendor/autoload.php';

// Use EmptyPlatform (without any types) platform
$mapper = new Mapper(new EmptyPlatform());
// You may also use platforms like:
//  - PostgreSQLPlatform
//  - ProtobufPlatform
//  - ...

$result = $mapper->normalize('example');

var_dump($result);
//
// TypeNotFoundException: Type "string" is not registered
//
