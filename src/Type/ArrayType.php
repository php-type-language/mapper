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

class ArrayType extends NamedType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'array';

    protected readonly TypeInterface $key;
    protected readonly bool $isKeyPassed;

    protected readonly TypeInterface $value;
    protected readonly bool $isValuePassed;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name = self::DEFAULT_TYPE_NAME,
        ?TypeInterface $key = null,
        ?TypeInterface $value = null,
    ) {
        parent::__construct($name);

        $this->key = $key ?? new ArrayKeyType();
        $this->isKeyPassed = $key !== null;

        $this->value = $value ?? new MixedType();
        $this->isValuePassed = $value !== null;
    }

    #[\Override]
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        $child = $context->withDetailedTypes(false);

        $arguments = [];

        if ($this->isKeyPassed) {
            $arguments[] = new TemplateArgumentNode(
                value: $this->key->getTypeStatement($child),
            );
        }

        if ($this->isValuePassed) {
            $arguments[] = new TemplateArgumentNode(
                value: $this->value->getTypeStatement($child),
            );
        }

        return new NamedTypeNode(
            name: $this->name,
            arguments: new TemplateArgumentsListNode($arguments),
        );
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            $context->enter(new ArrayIndexEntry($key));

            $isValidItem = $this->key->match($key, $context)
                && $this->value->match($value, $context);

            if (!$isValidItem) {
                return false;
            }

            $context->leave();
        }

        return true;
    }

    /**
     * @return array<array-key, mixed>
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

            $result[$this->key->cast($index, $context)]
                = $this->value->cast($item, $context);

            $context->leave();
        }

        return $result;
    }
}
