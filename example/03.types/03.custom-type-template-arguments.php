<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\DelegatePlatform;
use TypeLang\Mapper\Platform\Standard\Builder\Builder;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
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

        throw InvalidValueException::createFromContext($value, $context);
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

var_dump($mapper->normalize([], 'non-empty<string>'));
//
// InvalidValueException: Passed value [] is invalid
//


var_dump($mapper->normalize('example', 'non-empty'));
//
// MissingTemplateArgumentsException: Type "non-empty" expects at least 1
//                                    template argument(s), but 0 were passed
//

var_dump($mapper->normalize('', 'non-empty<string>'));
//
// InvalidValueException: Passed value "" is invalid
//
