<?php

declare(strict_types=1);

use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MapProperty;
use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Printer\PrettyPrinter;

require __DIR__ . '/../../vendor/autoload.php';

class ExampleDTO
{
    public function __construct(
        #[MapProperty(type: 'list<ExampleDTO>')]
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
    // Before: "list<ExampleDTO>"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type list<ExampleDTO>,
    // but 42 given at $.values[1].values

    // Print all NamedTypeNode AST statements as "!!!MODIFIED!!!" string
    $e->template->types = new class extends PrettyPrinter {
        protected function printNamedTypeNode(NamedTypeNode $node): string
        {
            return '!!!MODIFIED!!!';
        }
    };

    // After: "!!!MODIFIED!!!"
    var_dump($e->getMessage());
    // Passed value of field "values" must be of type !!!MODIFIED!!!,
    // but 42 given at $.values[1].values
}
