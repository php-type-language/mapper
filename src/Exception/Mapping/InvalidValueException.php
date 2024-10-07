<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Traverser;
use TypeLang\Parser\Traverser\TypeMapVisitor;

final class InvalidValueException extends MappingException implements ValueMappingExceptionInterface
{
    public function __construct(
        string $template,
        TypeStatement $expectedType,
        private TypeStatement $actualType,
        private mixed $actualValue,
        array $path = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            template: $template,
            expectedType: $expectedType,
            path: $path,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @param TypeStatement|non-empty-string $expectedType
     */
    public static function becauseInvalidValueGiven(
        LocalContext $context,
        TypeStatement|string $expectedType,
        mixed $actualValue,
        ?TypeStatement $actualType = null,
        bool $showValue = false,
    ): self {
        // @phpstan-ignore-next-line : False-positive, the "get_debug_type" always returns non-empty-string
        $actualType ??= new NamedTypeNode(\get_debug_type($actualValue));

        if (\is_string($expectedType)) {
            $expectedType = new NamedTypeNode($expectedType);
        }

        return new self(
            template: \sprintf(
                'Passed value must be of type {{expected}}, but {{actual}}%s given in {{path}}',
                $showValue ? '({{value}})' : '',
            ),
            expectedType: $expectedType,
            actualType: $actualType,
            actualValue: $actualValue,
            path: $context->getPathAsSegmentsArray(),
        );
    }

    /**
     * @param \Closure(Name):(Name|null) $transform
     */
    public function explain(callable $transform): static
    {
        Traverser::through(
            visitor: new TypeMapVisitor($transform(...)),
            nodes: [$this->actualType],
        );

        return parent::explain($transform);
    }

    protected function getReplacements(): array
    {
        return [
            ...parent::getReplacements(),
            'actual' => $this->getActualType(),
            'value' => $this->getActualValueAsString(),
        ];
    }

    /**
     * @api
     *
     * @return $this
     */
    public function setActualValueAndType(mixed $value, ?TypeStatement $type = null): self
    {
        $this->actualValue = $value;
        // @phpstan-ignore-next-line : False-positive, the "get_debug_type" always returns non-empty-string
        $this->actualType = $type ?? new NamedTypeNode(\get_debug_type($value));

        $this->updateMessage();

        return $this;
    }

    public function getActualType(): TypeStatement
    {
        return $this->actualType;
    }

    public function getActualValue(): mixed
    {
        return $this->actualValue;
    }

    public function getActualValueAsString(): string
    {
        if (\is_scalar($this->actualValue)) {
            return \var_export($this->actualValue, true);
        }

        return \get_debug_type($this->actualValue);
    }
}
