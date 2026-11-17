<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Message of the missing field of the property, the placeholder `{{field}}`
 * is replaced.
 *
 * ```
 * #[OnUndefinedError('Email is required')]
 * public string $email;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class OnUndefinedError
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $message,
    ) {}
}
