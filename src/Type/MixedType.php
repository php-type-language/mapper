<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;

/**
 * @template-implements TypeInterface<mixed>
 */
class MixedType implements TypeInterface
{
    public function match(mixed $value, RuntimeContext $context): MatchedResult
    {
        return MatchedResult::success($value);
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        $type = $context->getTypeByValue($value);

        return $type->cast($value, $context);
    }
}
