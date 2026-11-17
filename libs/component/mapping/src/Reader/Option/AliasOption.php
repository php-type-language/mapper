<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Exception\InvalidAliasException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;

/**
 * Aliases of the values of a backed enum: {@see MapAlias} of a case and the
 * `aliases` key with a map of the aliases to the values of the cases. An
 * alias is of the backing type, is not a value of a case and is given to
 * one case only, the aliased value is a value of a case.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class AliasOption implements ClassOptionInterface
{
    private const KEY = 'aliases';

    public function readClassAttributes(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassMetadata {
        $aliases = [];

        foreach ($class->getReflectionConstants() as $constant) {
            $attributes = $constant->getAttributes(MapAlias::class);

            if ($attributes === []) {
                continue;
            }

            $context = new SourceContext(
                target: $class->name . '::' . $constant->name,
                source: SourceInfo::createFromReflection($class),
            );
            $case = $constant->getValue();
            $value = $constant->name;

            if ($case instanceof \BackedEnum) {
                $value = $case->value;
            }

            foreach (AttributeFactory::create($attributes, MapAlias::class, $context, $errors) as $attribute) {
                foreach ($attribute->values as $alias) {
                    $aliases[] = [$alias, $value, $context];
                }
            }
        }

        return self::addAliases($class, $metadata, $aliases, $errors);
    }

    public function getClassKeys(): array
    {
        return [self::KEY];
    }

    /**
     * A key of a PHP array that looks like an integer is an integer, such a
     * key of an enum of strings is turned back into a string.
     */
    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassMetadata $metadata,
        ErrorCollector $errors,
    ): ClassMetadata {
        if (!$config->has(self::KEY)) {
            return $metadata;
        }

        $values = $config->get(self::KEY);

        if (!\is_array($values)) {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotMap(
                self::KEY,
                $class->name,
                $config->createContext(self::KEY)->source,
            ));

            return $metadata;
        }

        $enum = $class->name;
        $isStringEnum = \is_a($enum, \BackedEnum::class, true)
            && (string) (new \ReflectionEnum($enum))->getBackingType() === 'string';
        $aliases = [];

        foreach ($values as $alias => $value) {
            $context = $config->createContext(self::KEY, $alias);

            if (!\is_int($value) && !\is_string($value)) {
                $errors->addError(InvalidOptionValueException::becauseValueIsNotIntOrString(
                    self::KEY . '.' . $alias,
                    $class->name,
                    $context->source,
                ));

                continue;
            }

            if ($isStringEnum) {
                $alias = (string) $alias;
            }

            $aliases[] = [$alias, $value, $context];
        }

        return self::addAliases($class, $metadata, $aliases, $errors);
    }

    /**
     * Returns the metadata with the valid aliases, the errors of the others
     * are added.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg> $metadata
     * @param list<array{int|string, int|string, SourceContext}> $aliases
     *        alias, aliased value and the place of the alias
     * @return ClassMetadata<TArg>
     */
    private static function addAliases(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        array $aliases,
        ErrorCollector $errors,
    ): ClassMetadata {
        if ($aliases === []) {
            return $metadata;
        }

        $enum = $class->name;

        if (!\is_a($enum, \BackedEnum::class, true)) {
            $errors->addError(InvalidAliasException::becauseClassIsNotBackedEnum(
                target: $aliases[0][2]->target,
                source: $aliases[0][2]->source,
            ));

            return $metadata;
        }

        /** @var 'int'|'string' */
        $backing = (string) (new \ReflectionEnum($enum))->getBackingType();
        $given = [];

        foreach ($aliases as [$alias, $value, $context]) {
            $error = match (true) {
                \get_debug_type($alias) !== $backing => InvalidAliasException::becauseAliasIsNotOfBackingType(
                    alias: $alias,
                    backing: $backing,
                    target: $context->target,
                    source: $context->source,
                ),
                \get_debug_type($value) !== $backing || $enum::tryFrom($value) === null
                    => InvalidAliasException::becauseValueIsNotOfCase($value, $context->target, $context->source),
                $enum::tryFrom($alias) !== null
                    => InvalidAliasException::becauseAliasIsValueOfCase($alias, $context->target, $context->source),
                ($given[$alias] ?? $value) !== $value
                    => InvalidAliasException::becauseAliasIsDuplicated($alias, $context->target, $context->source),
                default => null,
            };

            if ($error !== null) {
                $errors->addError($error);

                continue;
            }

            $given[$alias] = $value;
            $metadata = $metadata->withAlias($alias, $value);
        }

        return $metadata;
    }
}
