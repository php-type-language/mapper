<?php

declare(strict_types=1);

namespace Serafim\Mapper\Exception\Mapping;

use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Exception\StringInfo;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class MissingRequiredFieldException extends MappingException implements FieldMappingExceptionInterface
{
    /**
     * @param non-empty-string $field
     * @param list<non-empty-string|int> $path
     */
    public function __construct(
        string $template,
        TypeStatement $expectedType,
        private string $field,
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
     * @param non-empty-string $field
     */
    public static function becauseFieldIsMissing(
        LocalContext $context,
        TypeStatement|string $expectedType,
        string $field,
    ): self {
        if (\is_string($expectedType)) {
            $expectedType = new NamedTypeNode($expectedType);
        }

        return new self(
            template: 'Object of type {{expected}} requires field {{field}} at {{path}}',
            expectedType: $expectedType,
            field: $field,
            path: $context->getPath(),
        );
    }

    protected function getReplacements(): array
    {
        return [
            ...parent::getReplacements(),
            'field' => StringInfo::quoted($this->field),
        ];
    }

    public function getFieldName(): string
    {
        return $this->field;
    }

    /**
     * @api
     * @param non-empty-string $name
     */
    public function setFieldName(string $name): self
    {
        $this->field = $name;
        $this->updateMessage();

        return $this;
    }
}
