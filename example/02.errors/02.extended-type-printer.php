<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Runtime\RuntimeException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Printer\PrettyPrinter;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        #[MapType(type: 'list<ExampleDTO>')]
        public readonly array $values = [],
    ) {}
}

$mapper = new Mapper();

try {
    $result = $mapper->denormalize([
        'values' => [
            ['values' => []],
            ['values' => 42],
        ],
    ], ExampleDTO::class);
} catch (RuntimeException $e) {
    var_dump($e->getMessage());

    // Print all NamedTypeNode AST statements as "!!!MODIFIED!!!" string
    $e->template->types = new class extends PrettyPrinter {
        protected function printNamedTypeNode(NamedTypeNode $node): string
        {
            return '!!!MODIFIED!!!';
        }
    };

    // Before: Passed value in "values" of {"values": 42} must be of type
    //         list<ExampleDTO>, but 42 given at $.values[1].values
    // After:  Passed value in "values" of {"values": 42} must be of type
    //         !!!MODIFIED!!!, but 42 given at $.values[1].values
    var_dump($e->getMessage());
}
