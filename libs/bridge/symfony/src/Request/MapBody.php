<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Request;

/**
 * Controller argument read from the JSON or form body of the request. An
 * empty body gives the default value or {@see null} of the argument, if
 * it has any.
 *
 * ```
 * public function create(#[MapBody] CreateUser $command): Response
 * ```
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapBody extends MapRequest {}
