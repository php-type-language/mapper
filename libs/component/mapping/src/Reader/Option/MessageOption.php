<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;

/**
 * Templates of the messages: {@see OnTypeError} and {@see OnUndefinedError}
 * of a property and the `type_error_message` and `undefined_error_message`
 * keys of its config, {@see OnTypeError} of a class and the
 * `type_error_message` key of its config.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class MessageOption implements PropertyOptionInterface, ClassOptionInterface
{
    private const TYPE_KEY = 'type_error_message';

    private const UNDEFINED_KEY = 'undefined_error_message';

    /**
     * Key of the message in the error of an attribute
     */
    private const ATTRIBUTE_KEY = 'message';

    public function hasPropertyAttributes(\ReflectionProperty $property): bool
    {
        return $property->getAttributes(OnTypeError::class) !== []
            || $property->getAttributes(OnUndefinedError::class) !== [];
    }

    public function readPropertyAttributes(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $types = AttributeFactory::create(
            $property->getAttributes(OnTypeError::class),
            OnTypeError::class,
            $context,
            $errors,
        );

        foreach ($types as $attribute) {
            $message = self::findMessage($attribute->message, self::ATTRIBUTE_KEY, $context, $errors);

            if ($message !== null) {
                $metadata = $metadata->withTypeErrorMessage($message);
            }
        }

        $undefined = AttributeFactory::create(
            $property->getAttributes(OnUndefinedError::class),
            OnUndefinedError::class,
            $context,
            $errors,
        );

        foreach ($undefined as $attribute) {
            $message = self::findMessage($attribute->message, self::ATTRIBUTE_KEY, $context, $errors);

            if ($message !== null) {
                $metadata = $metadata->withUndefinedErrorMessage($message);
            }
        }

        return $metadata;
    }

    public function getPropertyKeys(): array
    {
        return [self::TYPE_KEY, self::UNDEFINED_KEY];
    }

    public function readPropertyConfig(
        ConfigSection $config,
        PropertyMetadata $metadata,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $type = self::findConfigMessage($config, self::TYPE_KEY, $errors);

        if ($type !== null) {
            $metadata = $metadata->withTypeErrorMessage($type);
        }

        $undefined = self::findConfigMessage($config, self::UNDEFINED_KEY, $errors);

        if ($undefined !== null) {
            $metadata = $metadata->withUndefinedErrorMessage($undefined);
        }

        return $metadata;
    }

    public function readClassAttributes(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassMetadata {
        $attributes = $class->getAttributes(OnTypeError::class);

        if ($attributes === []) {
            return $metadata;
        }

        $context = $contexts->createFromClass($class);

        foreach (AttributeFactory::create($attributes, OnTypeError::class, $context, $errors) as $attribute) {
            $message = self::findMessage($attribute->message, self::ATTRIBUTE_KEY, $context, $errors);

            if ($message !== null) {
                $metadata = $metadata->withTypeErrorMessage($message);
            }
        }

        return $metadata;
    }

    public function getClassKeys(): array
    {
        return [self::TYPE_KEY];
    }

    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassMetadata $metadata,
        ErrorCollector $errors,
    ): ClassMetadata {
        $message = self::findConfigMessage($config, self::TYPE_KEY, $errors);

        if ($message === null) {
            return $metadata;
        }

        return $metadata->withTypeErrorMessage($message);
    }

    /**
     * @param non-empty-string $key
     * @return non-empty-string|null
     */
    private static function findConfigMessage(ConfigSection $config, string $key, ErrorCollector $errors): ?string
    {
        if (!$config->has($key)) {
            return null;
        }

        return self::findMessage($config->get($key), $key, $config->createContext($key), $errors);
    }

    /**
     * Returns {@see null} in case of the message is not a non-empty string,
     * the error is added.
     *
     * @param non-empty-string $key
     * @return non-empty-string|null
     */
    private static function findMessage(
        mixed $message,
        string $key,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?string {
        if (\is_string($message) && $message !== '') {
            return $message;
        }

        $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
            $key,
            $context->target,
            $context->source,
        ));

        return null;
    }
}
