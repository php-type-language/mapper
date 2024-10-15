<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

$mapper = new Mapper();

// Normalize date object to "d-m-Y" string.
$result = $mapper->normalize(new \DateTimeImmutable(), 'datetime<"d-m-Y">');

var_dump($result);
//
// string(10) "15-10-2024"
//
