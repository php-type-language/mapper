<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Access;

use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Reader\Reflection\ClassProperties;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\DefaultValueFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;

/**
 * Members declared by the `get` and `set` keys of a property config and by
 * the `methods` section. A property is accessed through its backing store,
 * `hook: true` chooses its hook. The name of the field of a method is
 * required.
 *
 * Only the members and their accesses are read here, the values of the keys
 * (`name` included) are read with the other options.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class ConfigAccessReader
{
    public const HOOK_KEY = 'hook';

    private const NAME_KEY = 'name';

    public function __construct(
        private readonly AccessFactory $factory,
    ) {}

    /**
     * Returns whether the config declares the directions by the `get` and
     * `set` keys.
     */
    public static function hasDirections(ConfigSection $config): bool
    {
        return $config->has(AccessDirection::Read->getConfigKey())
            || $config->has(AccessDirection::Write->getConfigKey());
    }

    /**
     * Reads the member of the property declared by the `get` and `set` keys,
     * {@see null} in case of the config has none of them.
     *
     * @param \ReflectionClass<object> $class
     */
    public function readProperty(
        \ReflectionClass $class,
        \ReflectionProperty $property,
        ConfigSection $config,
        ErrorCollector $errors,
    ): ?MemberDraft {
        $name = $property->name;
        $memberKey = ClassProperties::getKey($class, $property);

        if ($name === '' || $memberKey === '' || !self::hasDirections($config)) {
            return null;
        }

        // a static property is reported with the other keys of the config
        if ($property->isStatic()) {
            return null;
        }

        $accesses = [];

        foreach (AccessDirection::cases() as $direction) {
            $section = self::findSection($config, $direction, $errors);

            if ($section === null) {
                continue;
            }

            $key = $direction->getConfigKey();

            $accesses[$direction->name] = $this->factory->createPropertyAccess(
                property: $property,
                direction: $direction,
                isHook: self::findHookFlag($section, $errors),
                name: $name,
                isNameExplicit: false,
                context: $config->createContext($key),
                errors: $errors,
                key: $key,
            );
        }

        $member = new MemberDraft(
            key: $memberKey,
            read: $accesses[AccessDirection::Read->name] ?? null,
            write: $accesses[AccessDirection::Write->name] ?? null,
        );

        return DefaultValueFactory::withPropertyDefault($member, $property, $config->createSectionContext(), $errors);
    }

    /**
     * Reads the member of the method of the `methods` section: the `get` and
     * `set` keys or, without them, both directions.
     *
     * @param \ReflectionClass<object> $class
     */
    public function readMethod(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        ConfigSection $config,
        ErrorCollector $errors,
    ): ?MemberDraft {
        if (!self::hasDirections($config)) {
            return $this->readBothDirections($class, $method, $config, $errors);
        }

        $accesses = [];

        foreach (AccessDirection::cases() as $direction) {
            $section = self::findSection($config, $direction, $errors);
            $key = $direction->getConfigKey();

            if ($section === null) {
                continue;
            }

            if (!self::hasName($section, $key, $config->createContext($key)->source, $class, $method, $errors)) {
                continue;
            }

            $accesses[$direction->name] = $this->createMethodAccess(
                $class,
                $method,
                $direction,
                $config->createContext($key),
                $errors,
            );
        }

        return AccessFactory::createMethodMember(
            $method,
            $accesses[AccessDirection::Read->name] ?? null,
            $accesses[AccessDirection::Write->name] ?? null,
        );
    }

    /**
     * Keys without `get` and `set` declare both directions, the method that
     * cannot take one of them is an error.
     *
     * @param \ReflectionClass<object> $class
     */
    private function readBothDirections(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        ConfigSection $config,
        ErrorCollector $errors,
    ): ?MemberDraft {
        $context = $config->createContext(self::NAME_KEY);

        if (!self::hasName($config, $method->name, $context->source, $class, $method, $errors)) {
            return null;
        }

        if (!AccessFactory::isGetter($method) || !AccessFactory::isSetter($method)) {
            $errors->addError(InvalidOptionValueException::becauseMethodTakesOneDirection(
                AccessFactory::formatMethod($class, $method),
                $context->source,
            ));

            return null;
        }

        return AccessFactory::createMethodMember(
            $method,
            $this->createMethodAccess($class, $method, AccessDirection::Read, $context, $errors),
            $this->createMethodAccess($class, $method, AccessDirection::Write, $context, $errors),
        );
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function createMethodAccess(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        AccessDirection $direction,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?AccessDraft {
        return $this->factory->createMethodAccess(
            class: $class,
            method: $method,
            direction: $direction,
            name: $method->name,
            isNameExplicit: true,
            context: $context,
            errors: $errors,
        );
    }

    /**
     * Returns whether the section of the method gives the name of the field,
     * its value is checked with the other options.
     *
     * @param non-empty-string $key key of the section in the error
     * @param \ReflectionClass<object> $class
     */
    private static function hasName(
        ConfigSection $section,
        string $key,
        SourceInfo $source,
        \ReflectionClass $class,
        \ReflectionMethod $method,
        ErrorCollector $errors,
    ): bool {
        if ($section->has(self::NAME_KEY)) {
            return true;
        }

        $errors->addError(InvalidOptionValueException::becauseMethodRequiresName(
            $key,
            AccessFactory::formatMethod($class, $method),
            $source,
        ));

        return false;
    }

    /**
     * Returns the section of the key of the direction, a value that is not
     * a map is an error.
     */
    private static function findSection(
        ConfigSection $config,
        AccessDirection $direction,
        ErrorCollector $errors,
    ): ?ConfigSection {
        $key = $direction->getConfigKey();

        if (!$config->has($key)) {
            return null;
        }

        $section = $config->findSection($key);

        if ($section === null) {
            $context = $config->createContext($key);
            $errors->addError(InvalidOptionValueException::becauseValueIsNotMap(
                $key,
                $context->target,
                $context->source,
            ));
        }

        return $section;
    }

    private static function findHookFlag(ConfigSection $section, ErrorCollector $errors): bool
    {
        if (!$section->has(self::HOOK_KEY)) {
            return false;
        }

        $value = $section->get(self::HOOK_KEY);

        if (\is_bool($value)) {
            return $value;
        }

        $context = $section->createContext(self::HOOK_KEY);
        $errors->addError(InvalidOptionValueException::becauseValueIsNotBoolean(
            self::HOOK_KEY,
            $context->target,
            $context->source,
        ));

        return false;
    }
}
