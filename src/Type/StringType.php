<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\StringTypeCoercer;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;

/**
 * @template-extends ScalarType<string>
 */
class StringType extends ScalarType
{
    /**
     * @param TypeCoercerInterface<string> $coercer
     * @param TypeSpecifierInterface<string>|null $specifier
     */
    public function __construct(
        TypeCoercerInterface $coercer = new StringTypeCoercer(),
        ?TypeSpecifierInterface $specifier = null,
    ) {
        parent::__construct($coercer, $specifier);
    }

    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value);
    }
}
