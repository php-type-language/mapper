<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta;

use TypeLang\Mapper\Type\TypeInterface;

final class PropertyMetadata extends Metadata
{
    private mixed $defaultValue = null;

    private bool $hasDefaultValue = false;

    /**
     * @param non-empty-string $export
     * @throws \Exception
     */
    public function __construct(
        private string $export,
        private ?TypeInterface $type = null,
        ?int $createdAt = null,
    ) {
        parent::__construct($this->export, $createdAt);
    }

    /**
     * @api
     *
     * @param \ReflectionClass<object> $class
     *
     * @throws \ReflectionException
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
    public function withType(TypeInterface $type): self
    {
        $self = clone $this;
        $self->type = $type;

        return $self;
    }

    /**
     * @api
     */
    public function withoutType(): self
    {
        $self = clone $this;
        $self->type = null;

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
    public function hasType(): bool
    {
        return $this->type !== null;
    }
}
