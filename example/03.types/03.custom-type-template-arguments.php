<?php

declare(strict_types=1);

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\DelegatePlatform;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Builder\Builder;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

require __DIR__ . '/../../vendor/autoload.php';

// Create custom type builder
class MyNonEmptyTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        // Expects type with name "non-empty"
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === 'non-empty';
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
        // Shape fields not allowed (like: "non-empty{...}")
        $this->expectNoShapeFields($statement);
        // Expects only template argument (like: "non-empty<T>", but NOT "non-empty<T, U>")
        $this->expectTemplateArgumentsCount($statement, 1);

        $innerArgument = $statement->arguments->first();

        // inner type of TypeInterface
        $type = $types->getTypeByStatement($innerArgument->value);

        return new MyNonEmptyType($type);
    }
}

// Create custom type
class MyNonEmptyType implements TypeInterface
{
    public function __construct(
        private readonly TypeInterface $type,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return !empty($value);
    }

    public function cast(mixed $value, Context $context): mixed
    {
        if (!empty($value)) {
            return $this->type->cast($value, $context);
        }

        throw InvalidValueException::createFromContext($context);
    }
}

$mapper = new Mapper(new DelegatePlatform(
    delegate: new StandardPlatform(),
    // Extend by custom "MyNonEmptyTypeBuilder" type builder
    types: [new MyNonEmptyTypeBuilder()],
));

var_dump($mapper->normalize('example', 'non-empty<string>'));
//
// string(7) "example"
//

var_dump($mapper->normalize([1, 2, 3], 'non-empty<array>'));
//
// array:3 [
//  0 => 1
//  1 => 2
//  2 => 3
// ]
//

try {
    var_dump($mapper->normalize([], 'non-empty<string>'));
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
}
//
// InvalidValueException: Passed value [] is invalid
//

try {
    var_dump($mapper->normalize('example', 'non-empty'));
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
}
//
// MissingTemplateArgumentsException: Type "non-empty" expects at least 1
//                                    template argument(s), but 0 were passed
//

try {
    var_dump($mapper->normalize('', 'non-empty<string>'));
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
}
//
// InvalidValueException: Passed value "" is invalid
//
