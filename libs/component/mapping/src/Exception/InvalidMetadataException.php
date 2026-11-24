<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;

/**
 * Metadata of a class has wrong values, the errors describe what and where.
 */
final class InvalidMetadataException extends ReaderException
{
    private function __construct(
        /**
         * Metadata read despite the errors: wrong values are left out,
         * types with a failure are kept
         *
         * @var ClassDraft<object>
         */
        public readonly ClassDraft $metadata,
        /**
         * All errors of the class with their places, in order of appearance
         *
         * @var non-empty-list<MetadataException>
         */
        public readonly array $errors,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * @param ClassDraft<object> $metadata metadata read despite the
     *        errors
     * @param non-empty-list<MetadataException> $errors
     */
    public static function becauseMetadataIsInvalid(ClassDraft $metadata, array $errors): self
    {
        $message = \sprintf('The mapping for class "%s" contains errors:', $metadata->name);

        foreach ($errors as $error) {
            $message .= "\n  - " . $error->getMessage();
        }

        return new self($metadata, $errors, $message);
    }
}
