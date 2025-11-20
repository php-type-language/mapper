<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\UnionType;

use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed = TResult
 */
final class UnionSelection
{
    public function __construct(
        /**
         * @var TypeInterface<TResult>
         */
        public readonly TypeInterface $type,
        /**
         * @var MatchedResult<TMatch>
         */
        public readonly MatchedResult $result,
    ) {}
}
