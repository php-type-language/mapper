<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template T of mixed = mixed
 *
 * @template-implements TypeSpecifierInterface<T>
 */
final class NotSpecifier implements TypeSpecifierInterface
{
    public function __construct(
        /**
         * @var TypeSpecifierInterface<T>
         */
        private readonly TypeSpecifierInterface $delegate,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return !$this->delegate->match($value, $context);
    }
}
