<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class ClassStringType extends NamedType
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_TYPE_NAME = 'list';

    /**
     * @param non-empty-string $name
     * @param non-empty-string|null $class
     */
    public function __construct(
        string $name = self::DEFAULT_TYPE_NAME,
        private readonly ?string $class = null,
    ) {
        parent::__construct($name);
    }

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if (!$context->isDetailedTypes() || $this->class === null) {
            return parent::getTypeStatement($context);
        }

        return new NamedTypeNode($this->name, new TemplateArgumentsListNode([
            new TemplateArgumentNode(new NamedTypeNode(
                name: $this->class,
            )),
        ]));
    }

    public function match(mixed $value, LocalContext $context): bool
    {
        $isValidString = $value !== '' && \is_string($value);

        if (!$isValidString) {
            return false;
        }

        if ($this->class === null) {
            return \class_exists($value);
        }

        return \is_a($value, $this->class, true);
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): string
    {
        if ($this->match($value, $context)) {
            /** @var class-string */
            return $value;
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
