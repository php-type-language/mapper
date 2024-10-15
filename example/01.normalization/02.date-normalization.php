<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;

require __DIR__ . '/../../vendor/autoload.php';

$mapper = new Mapper();

// Normalize date object to default RFC3339 string.
$result = $mapper->normalize(new \DateTimeImmutable());

var_dump($result);
//
// string(25) "2024-06-29T16:16:41+00:00"
//
