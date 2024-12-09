<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class NonInstantiatableObjectException extends ObjectException
{
    /**
     * @param \ReflectionClass<object> $value
     */
    public function __construct(
        TypeStatement $expected,
        \ReflectionClass $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($expected, $value, $path, $template, $code, $previous);
    }

    /**
     * @param \ReflectionClass<object> $value
     */
    public static function createFromPath(
        ?TypeStatement $expected,
        \ReflectionClass $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = \sprintf('Unable to instantiate %s of {{expected}}', match (true) {
            $value->isAbstract() => 'abstract class',
            $value->isInterface() => 'interface',
            default => 'unknown non-instantiable type',
        });

        return new self(
            expected: $expected ?? self::mixedTypeStatement(),
            value: $value,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @param \ReflectionClass<object> $value
     */
    public static function createFromContext(
        ?TypeStatement $expected,
        \ReflectionClass $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            expected: $expected,
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
