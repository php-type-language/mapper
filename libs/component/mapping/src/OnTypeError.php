<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

/**
 * Message of an invalid value: of the property, or of the class as a whole.
 * The placeholders of the violation are replaced, such as `{{value}}` and
 * `{{expected}}`.
 *
 * ```
 * #[OnTypeError('Age must be a number, {{value}} given')]
 * public int $age;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
final class OnTypeError
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $message,
    ) {}
}
