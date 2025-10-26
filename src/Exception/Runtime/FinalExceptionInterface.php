<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Exception\MapperExceptionInterface;

/**
 * Any exception described by this interface MUST NOT be qualified by
 * a parent composite type.
 *
 * For example, if a field data error occurs in an object inside a
 * field of another object.
 *
 * ```
 * // [Level#1] If the inner exception was marked as "final", no decoration is
 * // required and the exception from "[Level#1]" will be thrown, like:
 * // - "Invalid value INVALID_VALUE in `field` of object `child`".
 * //
 * // If the inner exception had NOT been marked as "final", the
 * // exception would have been decorated and would have looked like:
 * // - "Invalid value {field: INVALID_VALUE} in `child` of object `object`".
 * object: {
 *     // [Level#2] There is the "final" exception that should not have been
 *     // decorated further, like:
 *     // - "Invalid value INVALID_VALUE in `field` of object `child`".
 *     child: {
 *         // [Level#3] There is not a final exception, like:
 *         // - "Invalid value INVALID_VALUE".
 *         field: INVALID_VALUE
 *     }
 * }
 * ```
 */
interface FinalExceptionInterface extends MapperExceptionInterface {}
