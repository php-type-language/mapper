<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Wrong value found in a source of metadata of a class.
 */
abstract class MetadataException extends ReaderException
{
    /**
     * @param string $message reason of the error, the message is
     *        `target: reason in place`
     */
    protected function __construct(
        /**
         * Property or class the wrong value belongs to: `App\User::$id`,
         * `App\User`
         */
        public readonly string $target,
        /**
         * Place of the wrong value
         */
        public readonly SourceInfo $source,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($target . ': ' . $message . self::formatPlace($source), 0, $previous);
    }

    /**
     * Returns the property reference used as {@see $target}: `App\User::$id`.
     *
     * @internal this is an internal library method, please do not use it in your code
     * @psalm-internal TypeLang\Mapper\Mapping\Reader
     *
     * @return non-empty-string
     */
    public static function formatPropertyTarget(string $class, string $property): string
    {
        return $class . '::$' . $property;
    }

    private static function formatPlace(SourceInfo $source): string
    {
        $place = $source->findPlace();

        if ($place === null) {
            return '';
        }

        return ' in ' . $place;
    }
}
