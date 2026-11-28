<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Reader\Access\AccessDirection;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Sensitive;

/**
 * Hidden rejected value of the field of the denormalization:
 * {@see Sensitive} and the `sensitive` key. A {@see \SensitiveParameter} of
 * a promoted constructor parameter is read by the reflection reader, of a
 * setter by the readers of the accesses, with the helpers of the option.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class SensitiveOption implements PropertyOptionInterface
{
    private const KEY = 'sensitive';

    /**
     * Tells whether the property is promoted from a constructor parameter
     * with a {@see \SensitiveParameter}.
     */
    public static function isSensitiveProperty(\ReflectionProperty $property): bool
    {
        if (!$property->isPromoted()) {
            return false;
        }

        $parameter = new \ReflectionParameter([$property->class, '__construct'], $property->name);

        return self::isSensitiveParameter($parameter);
    }

    public static function isSensitiveParameter(\ReflectionParameter $parameter): bool
    {
        return $parameter->getAttributes(\SensitiveParameter::class) !== [];
    }

    public function getPropertyAttributes(): array
    {
        return [Sensitive::class => AccessDirection::Write];
    }

    public function readPropertyAttributes(
        \ReflectionProperty $property,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        $attributes = AttributeFactory::create(
            $property->getAttributes(Sensitive::class),
            Sensitive::class,
            $context,
            $errors,
        );

        foreach ($attributes as $attribute) {
            $metadata = $metadata->withSensitive($attribute->enabled);
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

        $value = $config->get(self::KEY);

        if (\is_bool($value)) {
            return $metadata->withSensitive($value);
        }

        $context = $config->createContext(self::KEY);
        $errors->addError(InvalidOptionValueException::becauseValueIsNotBoolean(
            self::KEY,
            $context->target,
            $context->source,
        ));

        return $metadata;
    }
}
