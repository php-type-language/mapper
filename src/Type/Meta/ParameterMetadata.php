<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Meta;

abstract class ParameterMetadata
{
    private mixed $defaultValue = null;

    private bool $hasDefaultValue = false;

    /**
     * @param int<0, max> $position
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly int $position,
        private readonly string $name,
    ) {}

    /**
     * @api
     *
     * @param \ReflectionClass<object> $context
     *
     * @throws \InvalidArgumentException
     */
    public function getReflection(\ReflectionClass $context): \ReflectionParameter
    {
        $constructor = $context->getConstructor();

        if ($constructor === null) {
            throw $this->invalidReflectionClassException($context);
        }

        $parameters = $constructor->getParameters();

        return $parameters[$this->position]
            ?? throw $this->invalidReflectionClassException($context);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return \InvalidArgumentException
     */
    private function invalidReflectionClassException(\ReflectionClass $class): \InvalidArgumentException
    {
        return new \InvalidArgumentException(\sprintf(
            'Parameter $%s is not part of the passed class %s',
            $this->name,
            $class->isAnonymous() ? 'class@anonymous' : $class->getName(),
        ));
    }

    /**
     * @return int<0, max>
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
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
}
