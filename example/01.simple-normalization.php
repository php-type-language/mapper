<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../vendor/autoload.php';

// An example of converting arbitrary data to primitive data.
//
// In this case, the date object is converted to a string.

$mapper = new Mapper();

// Normalize date object to default RFC3339 string.
$result = $mapper->normalize(new \DateTimeImmutable());

var_dump($result);
//
// string(25) "2024-06-29T16:16:41+00:00"
//



// Note that the date type is registered and accepts additional
// formatting arguments.

// Normalize date object to "d-m-Y" string.
$result = $mapper->normalize(new \DateTimeImmutable(), 'DateTime<"d-m-Y">');

var_dump($result);
//
// string(10) "29-06-2024"
//
