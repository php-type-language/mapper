<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * The property is left out of the normalized data in case of its value is
 * {@see null}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class SkipWhenNull {}
