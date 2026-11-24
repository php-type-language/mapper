<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidDefaultValueException;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\DefaultValueFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;

/**
 * Default value of a property from the `default` key: {@see null}, a
 * scalar, an enum case or an array of them. The default value of the
 * declaration is read by the reflection reader.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class DefaultOption implements PropertyOptionInterface
{
    private const KEY = 'default';

    public function getPropertyAttributes(): array
    {
        return [];
    }

    public function readPropertyAttributes(
        \ReflectionProperty $property,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        return $metadata;
    }

    public function getPropertyKeys(): array
    {
        return [self::KEY];
    }

    public function readPropertyConfig(
        ConfigSection $config,
        MemberDraft $metadata,
        ErrorCollector $errors,
    ): MemberDraft {
        if (!$config->has(self::KEY)) {
            return $metadata;
        }

        try {
            $value = DefaultValueFactory::createFromValue($config->get(self::KEY), $config->createContext(self::KEY));
        } catch (InvalidDefaultValueException $e) {
            $errors->addError($e);

            return $metadata;
        }

        return $metadata->withDefaultValue($value);
    }
}
