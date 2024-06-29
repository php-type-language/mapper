<?php

declare(strict_types=1);

namespace Serafim\Mapper\Meta;

use Serafim\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class PropertyMetadata extends Metadata
{
    private mixed $defaultValue = null;

    private bool $hasDefaultValue = false;

    /**
     * @var non-empty-string
     */
    private string $export;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name,
        private ?TypeInterface $type = null,
        private ?TypeStatement $statement = null,
        ?int $createdAt = null,
    ) {
        $this->export = $name;

        parent::__construct($name, $createdAt);
    }

    /**
     * @api
     */
    public function getReflection(\ReflectionClass $class): \ReflectionProperty
    {
        return $class->getProperty($this->getName());
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function withExportName(string $name): self
    {
        $self = clone $this;
        $self->export = $name;

        return $self;
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getExportName(): string
    {
        return $this->export;
    }

    /**
     * @api
     */
    public function withDefaultValue(mixed $value): self
    {
        $self = clone $this;
        $self->defaultValue = $value;
        $self->hasDefaultValue = true;

        return $self;
    }

    /**
     * @api
     */
    public function withoutDefaultValue(): self
    {
        $self = clone $this;
        $self->defaultValue = null;
        $self->hasDefaultValue = false;

        return $self;
    }

    /**
     * @api
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @api
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * @api
     */
    public function withType(TypeInterface $type, TypeStatement $statement): self
    {
        $self = clone $this;
        $self->type = $type;
        $self->statement = $statement;

        return $self;
    }

    /**
     * @api
     */
    public function withTypeStatement(TypeStatement $statement): self
    {
        $self = clone $this;
        $self->statement = $statement;

        return $self;
    }

    /**
     * @api
     */
    public function withoutType(): self
    {
        $self = clone $this;
        $self->type = null;
        $self->statement = null;

        return $self;
    }

    /**
     * @api
     */
    public function getType(): ?TypeInterface
    {
        return $this->type;
    }

    /**
     * @api
     */
    public function getTypeStatement(): ?TypeStatement
    {
        return $this->statement;
    }

    /**
     * @api
     */
    public function hasType(): bool
    {
        return $this->type !== null;
    }
}
