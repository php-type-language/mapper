<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;

use TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

/**
 * @phpstan-import-type PropertyConfigType from ArrayConfigDriver
 */
final class ErrorMessagePropertyConfigLoader extends PropertyConfigLoader
{
    public function load(
        array $config,
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $this->loadTypeErrorMessage($config, $metadata);
        $this->loadUndefinedErrorMessage($config, $metadata);
    }

    /**
     * @param PropertyConfigType $config
     */
    private function loadTypeErrorMessage(array $config, PropertyMetadata $metadata): void
    {
        if (!\array_key_exists('type_error_message', $config)) {
            return;
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_string($config['type_error_message']));

        $metadata->typeErrorMessage = $config['type_error_message'];
    }

    /**
     * @param PropertyConfigType $config
     */
    private function loadUndefinedErrorMessage(array $config, PropertyMetadata $metadata): void
    {
        if (!\array_key_exists('undefined_error_message', $config)) {
            return;
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_string($config['undefined_error_message']));

        $metadata->undefinedErrorMessage = $config['undefined_error_message'];
    }
}
