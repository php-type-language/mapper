<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template TSupportedValue of mixed = mixed
 *
 * @template-implements TypeSpecifierInterface<TSupportedValue>
 */
final class AllOfSpecifier implements TypeSpecifierInterface
{
    public function __construct(
        /**
         * @var non-empty-list<TypeSpecifierInterface<TSupportedValue>>
         */
        private readonly array $matchers,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        foreach ($this->matchers as $matcher) {
            if (!$matcher->match($value, $context)) {
                return false;
            }
        }

        return true;
    }
}
