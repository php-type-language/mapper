<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed = mixed
 *
 * @template-extends LiteralType<TResult, TMatch>
 */
class ClassConstType extends LiteralType {}
