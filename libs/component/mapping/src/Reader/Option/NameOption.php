<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;

/**
 * External name of a property: {@see MapName} and the `name` key.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class NameOption implements PropertyOptionInterface
{
    private const KEY = 'name';

    public function getPropertyAttributes(): array
    {
        return [MapName::class => null];
    }

    public function readPropertyAttributes(
        \ReflectionProperty $property,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        $attributes = AttributeFactory::create(
            $property->getAttributes(MapName::class),
            MapName::class,
            $context,
            $errors,
        );

        foreach ($attributes as $attribute) {
            $metadata = self::apply($attribute->name, $metadata, $context, $errors);
        }

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

        return self::apply($config->get(self::KEY), $metadata, $config->createContext(self::KEY), $errors);
    }

    private static function apply(
        mixed $name,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        if (\is_string($name) && $name !== '') {
            return $metadata->withName($name);
        }

        $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
            self::KEY,
            $context->target,
            $context->source,
        ));

        return $metadata;
    }
}
