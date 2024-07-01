<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class TypeNotFoundException extends TypeException
{
    public function __construct(
        string $template,
        private readonly TypeStatement $expectedType,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($template, $code, $previous);
    }

    public function getExpectedType(): TypeStatement
    {
        return $this->expectedType;
    }

    protected function getReplacements(): array
    {
        return [
            ...parent::getReplacements(),
            'expected' => $this->getExpectedType(),
        ];
    }

    public static function fromType(TypeStatement $type, ?\Throwable $prev = null): self
    {
        return new self(
            template: 'Type {{expected}} is not registered',
            expectedType: $type,
            previous: $prev,
        );
    }

    public static function fromPropertyType(
        string $class,
        string $property,
        TypeStatement $type,
        ?\Throwable $prev = null
    ): self {
        return new self(
            template: \vsprintf('Type {{expected}} for property %s::$%s is not registered', [
                $class,
                $property,
            ]),
            expectedType: $type,
            previous: $prev,
        );
    }
}
