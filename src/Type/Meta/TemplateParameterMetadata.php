<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type\Meta;

final class TemplateParameterMetadata extends ParameterMetadata
{
    /**
     * @var list<non-empty-string>
     */
    private array $allowedIdentifiers = [];

    /**
     * @api
     *
     * @param non-empty-string $identifier
     */
    public function withAllowedIdentifier(string $identifier): self
    {
        $self = clone $this;
        $self->allowedIdentifiers = \array_values(\array_unique([
            ...$self->allowedIdentifiers,
            $identifier,
        ]));

        return $self;
    }

    /**
     * @api
     *
     * @return list<non-empty-string>
     */
    public function getAllowedIdentifiers(): array
    {
        return $this->allowedIdentifiers;
    }
}
