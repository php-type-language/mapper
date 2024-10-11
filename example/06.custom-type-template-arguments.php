<?php

declare(strict_types=1);

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\DelegatePlatform;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Mapper\Type\Builder\Builder;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

require __DIR__ . '/../vendor/autoload.php';

// You can also add your own types.

class MyNonEmptyTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === 'non-empty';
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): TypeInterface
    {
        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsCount($statement, 1);

        $innerArgument = $statement->arguments->first();

        return new MyNonEmptyType(
            type: $types->getByStatement($innerArgument->value),
        );
    }
}

class MyNonEmptyType implements TypeInterface
{
    public function __construct(
        private readonly TypeInterface $type,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        // Returns "non-empty<T>" type representation.
        return new NamedTypeNode('non-empty', new TemplateArgumentsListNode([
            new TemplateArgumentNode($this->type->getTypeStatement($context)),
        ]));
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return !empty($value);
    }

    public function cast(mixed $value, LocalContext $context): mixed
    {
        if (!empty($value)) {
            return $this->type->cast($value, $context);
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}

$mapper = new Mapper(new DelegatePlatform(
    delegate: new StandardPlatform(),
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
// InvalidValueException: Passed value must be of type non-empty, but
//                        array given at root.
//


var_dump($mapper->normalize('example', 'non-empty'));
//
// MissingTemplateArgumentsException: Type "non-empty" expects 1 template
//                                    argument, but only 0 were passed
//

var_dump($mapper->normalize('', 'non-empty<string>'));
//
// InvalidValueException: Passed value must be of type non-empty, but
//                        null given at root.
//
