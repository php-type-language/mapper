<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

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
         * @var ClassMetadata<object>
         */
        public readonly ClassMetadata $metadata,
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
     * @param ClassMetadata<object> $metadata metadata read despite the
     *        errors
     * @param non-empty-list<MetadataException> $errors
     */
    public static function becauseMetadataIsInvalid(ClassMetadata $metadata, array $errors): self
    {
        $message = \sprintf('Cannot read metadata of "%s":', $metadata->name);

        foreach ($errors as $error) {
            $message .= "\n  - " . $error->getMessage();
        }

        return new self($metadata, $errors, $message);
    }
}
