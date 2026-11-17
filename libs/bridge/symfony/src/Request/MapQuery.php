<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Request;

/**
 * Controller argument read from the query string of the request. The
 * values of a query string are strings, a strict mapper rejects them for
 * other scalar types.
 *
 * ```
 * public function list(#[MapQuery] UserFilter $filter): Response
 * ```
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapQuery extends MapRequest {}
