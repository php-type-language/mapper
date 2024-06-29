<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Meta;

use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template T of TypeInterface
 */
final class TypeMetadata
{
    /**
     * @var array<non-empty-string, ParameterMetadata>
     */
    public array $parameters = [];

    private bool $shapeFieldsIsAllowed = false;

    private bool $unsealedShapeIsAllowed = false;

    private bool $templateArgumentsIsAllowed = false;

    /**
     * @param class-string<T> $name
     * @param array<non-empty-string, ParameterMetadata> $parameters
     */
    public function __construct(
        private readonly string $name,
        iterable $parameters = [],
    ) {
        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
    }

    /**
     * @api
     */
    public function isShapeFieldsIsAllowed(): bool
    {
        return $this->shapeFieldsIsAllowed;
    }

    /**
     * @api
     */
    public function isUnsealedShapeIsAllowed(): bool
    {
        return $this->unsealedShapeIsAllowed;
    }

    /**
     * @api
     */
    public function isTemplateArgumentsIsAllowed(): bool
    {
        return $this->templateArgumentsIsAllowed;
    }

    /**
     * @param callable(ParameterMetadata):bool $filter
     */
    private function findParameter(callable $filter): ?ParameterMetadata
    {
        foreach ($this->parameters as $parameter) {
            if ($filter($parameter)) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * @api
     *
     * @template TParam of ParameterMetadata
     *
     * @param class-string<TParam> $class
     *
     * @return TParam|null
     */
    public function findParameterByType(string $class): ?ParameterMetadata
    {
        /** @var TParam|null */
        return $this->findParameter(static fn(ParameterMetadata $meta): bool
            => $meta instanceof $class);
    }

    /**
     * @param callable(ParameterMetadata):bool $filter
     *
     * @return int<0, max>
     */
    private function getNumberOfParameters(callable $filter): int
    {
        $count = 0;

        foreach ($this->parameters as $parameter) {
            if ($filter($parameter)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @api
     *
     * @return int<0, max>
     */
    public function getNumberOfTemplateParameters(): int
    {
        return $this->getNumberOfParameters(static fn(ParameterMetadata $meta): bool
            => $meta instanceof TemplateParameterMetadata);
    }

    /**
     * @api
     *
     * @return int<0, max>
     */
    public function getNumberOfRequiredTemplateParameters(): int
    {
        return $this->getNumberOfParameters(static fn(ParameterMetadata $meta): bool
            => $meta instanceof TemplateParameterMetadata && !$meta->hasDefaultValue());
    }

    /**
     * @api
     *
     * @return int<0, max>
     */
    public function getNumberOfOptionalTemplateParameters(): int
    {
        return $this->getNumberOfParameters(static fn(ParameterMetadata $meta): bool
            => $meta instanceof TemplateParameterMetadata && $meta->hasDefaultValue());
    }

    /**
     * @api
     *
     * @return class-string<T>
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @api
     *
     * @return \ReflectionClass<T>
     * @throws \ReflectionException
     */
    public function getReflection(): \ReflectionClass
    {
        return new \ReflectionClass($this->getName());
    }

    private function addParameter(ParameterMetadata $parameter): void
    {
        switch (true) {
            case $parameter instanceof ShapeFieldsParameterMetadata:
                $this->shapeFieldsIsAllowed = true;
                break;
            case $parameter instanceof SealedShapeFlagParameterMetadata:
                $this->shapeFieldsIsAllowed = $this->unsealedShapeIsAllowed = true;
                break;
            case $parameter instanceof TemplateParameterMetadata:
                $this->templateArgumentsIsAllowed = true;
                break;
        }

        $this->parameters[$parameter->getName()] = $parameter;
    }

    /**
     * @api
     *
     * @return self<T>
     */
    public function withAddedParameter(ParameterMetadata $parameter): self
    {
        $self = clone $this;
        $self->addParameter($parameter);

        return $self;
    }

    /**
     * @api
     *
     * @param non-empty-string $name
     */
    public function findParameterByName(string $name): ?ParameterMetadata
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * @return list<ParameterMetadata>
     */
    public function getParameters(): array
    {
        return \array_values($this->parameters);
    }
}
