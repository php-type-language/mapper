<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * The property is left out of the normalized data in case of its value is
 * empty by the rules of PHP `empty()`.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class SkipWhenEmpty {}
