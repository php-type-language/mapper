<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

/**
 * Kinds of violations of the mapping itself.
 */
enum ViolationCode: string implements ViolationCodeInterface
{
    /**
     * Value is not of the expected type, including a union where no branch
     * has matched.
     */
    case InvalidType = 'invalid_type';

    /**
     * Key of a collection is not of the expected key type.
     */
    case InvalidKey = 'invalid_key';

    /**
     * Required field is absent, the path points to the field itself.
     */
    case MissingField = 'missing_field';

    /**
     * Field is not expected by the target.
     */
    case UnexpectedField = 'unexpected_field';

    /**
     * Field holds the value of a target already given by another field, such
     * as an alias of a property next to its name.
     */
    case DuplicateField = 'duplicate_field';

    /**
     * Value of the expected type fails a check of its refinement, such as
     * `positive-int`.
     */
    case FailedCheck = 'failed_check';

    /**
     * Value of the expected type is rejected.
     */
    case InvalidValue = 'invalid_value';

    public function getCode(): string
    {
        return $this->value;
    }

    public function getTemplate(): string
    {
        return match ($this) {
            self::InvalidType => 'Value must be of type "{{expected}}", but {{value}} given',
            self::InvalidKey => 'Key must be of type "{{expected}}", but {{value}} given',
            self::MissingField => 'Required field "{{field}}" is missing',
            self::UnexpectedField => 'Unexpected field "{{field}}"',
            self::DuplicateField => 'Field "{{field}}" duplicates the field "{{other}}"',
            self::FailedCheck => 'Value must be of type "{{expected}}" ({{check}})',
            self::InvalidValue => '{{message}}',
        };
    }
}
