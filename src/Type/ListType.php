<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Mapper\Runtime\Path\Entry\ArrayIndexEntry;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class ListType extends NamedType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'list';

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name = self::DEFAULT_TYPE_NAME,
        private readonly TypeInterface $type = new MixedType(),
    ) {
        parent::__construct($name);
    }

    #[\Override]
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        $child = $context->withDetailedTypes(false);

        return new NamedTypeNode(
            name: $this->name,
            arguments: new TemplateArgumentsListNode([
                new TemplateArgumentNode($this->type->getTypeStatement($child)),
            ]),
        );
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        return \is_array($value) && \array_is_list($value);
    }

    /**
     * @return list<mixed>
     * @throws InvalidValueException
     * @throws TypeNotFoundException
     */
    public function cast(mixed $value, LocalContext $context): array
    {
        if (!\is_iterable($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        $result = [];

        foreach ($value as $index => $item) {
            $context->enter(new ArrayIndexEntry($index));

            $result[] = $this->type->cast($item, $context);

            $context->leave();
        }

        return $result;
    }
}
