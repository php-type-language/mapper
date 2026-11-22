<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Direction;
use TypeLang\Mapper\Mapping\Exception\InvalidAccessMethodException;
use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapOnly;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\Metadata\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\AccessPointInterface;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\HookAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\AccessPoint\MethodAccessPoint;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Option\AccessOption\AccessDirection;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Reader\Exception\ReaderExceptionInterface;
use TypeLang\Reader\PropertyAccessDirection;
use TypeLang\Reader\ReaderInterface;
use TypeLang\Type\TypeNode;

/**
 * Access points chosen by the user and the native types at them.
 * {@see MapGetter} of the `get` hook and {@see MapSetter} of the `set` hook
 * access the property through the hook, of a method through the method
 * that names the property. {@see MapOnly} leaves the property out of the
 * other direction. The `getter` and `setter` keys name the method or, with
 * {@see true}, the hook, the `only` key is the direction. Two points of one
 * direction, a point of the direction left out, the only direction the
 * property does not take part in and a method that cannot be the getter or
 * the setter are errors.
 *
 * Unlike the other options it is read in a phase of its own, before them:
 * the other options read the types at the chosen points.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class AccessOption
{
    private const ONLY_KEY = 'only';

    public function __construct(
        /**
         * Reader of the native types of the hooks and methods
         */
        private readonly ReaderInterface $nativeTypeReader,
    ) {}

    /**
     * Reads {@see MapGetter} and {@see MapSetter} of the hooks and the
     * methods, then {@see MapOnly} of the properties.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg> $metadata
     * @return ClassMetadata<TArg>
     */
    public function readAttributes(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassMetadata {
        $read = AccessDirection::Read;
        $write = AccessDirection::Write;
        $getters = $setters = [];

        foreach ($class->getProperties() as $property) {
            $getters = $this->readHook($class, $property, $read, $metadata, $getters, $contexts, $errors);
            $setters = $this->readHook($class, $property, $write, $metadata, $setters, $contexts, $errors);
        }

        foreach ($class->getMethods() as $method) {
            $getters = $this->readMethod($class, $method, $read, $metadata, $getters, $contexts, $errors);
            $setters = $this->readMethod($class, $method, $write, $metadata, $setters, $contexts, $errors);
        }

        $metadata = self::withPoints($metadata, $read, $getters);
        $metadata = self::withPoints($metadata, $write, $setters);

        foreach ($class->getProperties() as $property) {
            $metadata = self::readOnly($class, $property, $metadata, $getters, $setters, $contexts, $errors);
        }

        return $metadata;
    }

    /**
     * Returns the keys of a property config read by the option.
     *
     * @return list<non-empty-string>
     */
    public function getConfigKeys(): array
    {
        return [AccessDirection::Read->getConfigKey(), AccessDirection::Write->getConfigKey(), self::ONLY_KEY];
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    public function readConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        PropertyMetadata $metadata,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $only = self::findConfigOnly($config, $errors);

        foreach (AccessDirection::cases() as $direction) {
            $key = $direction->getConfigKey();

            if (!$config->has($key)) {
                continue;
            }

            $context = $config->createContext($key);

            if ($only === $direction->getOther()) {
                $errors->addError(InvalidOptionValueException::becauseDirectionIsLeftOut(
                    $key,
                    self::ONLY_KEY,
                    $context->target,
                    $context->source,
                ));

                continue;
            }

            $metadata = $this->readConfigPoint($class, $config->get($key), $direction, $metadata, $context, $errors);
        }

        if ($only === null) {
            return $metadata;
        }

        if ($only->findAccess($metadata) !== null) {
            return $only->getOther()->withAccess($metadata, null);
        }

        $context = $config->createContext(self::ONLY_KEY);
        $errors->addError(InvalidOptionValueException::becauseDirectionIsMissing(
            self::ONLY_KEY,
            $only->getParticiple(),
            $context->target,
            $context->source,
        ));

        return $metadata;
    }

    /**
     * Adds the hook of the direction chosen by its attribute to the points.
     *
     * @param \ReflectionClass<object> $class
     * @param ClassMetadata<object> $metadata
     * @param array<non-empty-string, AccessMetadata> $points
     * @return array<non-empty-string, AccessMetadata>
     */
    private function readHook(
        \ReflectionClass $class,
        \ReflectionProperty $property,
        AccessDirection $direction,
        ClassMetadata $metadata,
        array $points,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): array {
        $hook = $direction->findHook($property);
        $attribute = $direction->getAttribute();
        $attributes = $hook?->getAttributes($attribute) ?? [];
        $name = $property->name;

        if ($hook === null || $attributes === [] || $name === '') {
            return $points;
        }

        $context = $contexts->createFromReflection($class, $property);
        $source = SourceInfo::createFromReflection($hook);
        $instance = AttributeFactory::create($attributes, $attribute, $context, $errors)[0] ?? null;

        if ($instance === null || self::findMapped($metadata, $name, $context->target, $source, $errors) === null) {
            return $points;
        }

        if ($instance->property !== null) {
            $errors->addError(InvalidAttributeException::becauseHookTakesNoPropertyName(
                attribute: $attribute,
                property: $context->target,
                source: $source,
            ));

            return $points;
        }

        $type = $this->findPropertyType($property, $direction->getNativeDirection(), $context);
        $points[$name] = new AccessMetadata(new HookAccessPoint($name), $type);

        return $points;
    }

    /**
     * Adds the method chosen by the attribute of the direction to the
     * points, a second point of a property is an error.
     *
     * @param \ReflectionClass<object> $class
     * @param ClassMetadata<object> $metadata
     * @param array<non-empty-string, AccessMetadata> $points
     * @return array<non-empty-string, AccessMetadata>
     */
    private function readMethod(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        AccessDirection $direction,
        ClassMetadata $metadata,
        array $points,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): array {
        $attribute = $direction->getAttribute();
        $attributes = $method->getAttributes($attribute);

        if ($attributes === []) {
            return $points;
        }

        $reference = self::formatMethod($class, $method);
        $source = SourceInfo::createFromReflection($method);
        $context = new SourceContext($reference, $source);
        $instance = AttributeFactory::create($attributes, $attribute, $context, $errors)[0] ?? null;

        if ($instance === null) {
            return $points;
        }

        $name = $instance->property;

        if ($name === null || $name === '') {
            $errors->addError(InvalidAttributeException::becauseMethodRequiresPropertyName(
                attribute: $attribute,
                method: $reference,
                source: $source,
            ));

            return $points;
        }

        $target = MetadataException::formatPropertyTarget($class->name, $name);

        if (self::findMapped($metadata, $name, $target, $source, $errors) === null) {
            return $points;
        }

        if (isset($points[$name])) {
            $errors->addError(InvalidAttributeException::becauseAccessMethodIsDuplicated(
                attribute: $attribute,
                property: $target,
                method: $reference,
                source: $source,
            ));

            return $points;
        }

        $error = self::findMethodError($class, $method, $direction, $target, $source);

        if ($error !== null) {
            $errors->addError($error);

            return $points;
        }

        $type = $this->findMethodType($method, $direction, $contexts->createFromMethod($class, $method, $name));
        $points[$name] = new AccessMetadata(new MethodAccessPoint($method->name), $type);

        return $points;
    }

    /**
     * Returns the copy with the chosen points of the direction.
     *
     * @template TArg of object
     * @param ClassMetadata<TArg> $metadata
     * @param array<non-empty-string, AccessMetadata> $points
     * @return ClassMetadata<TArg>
     */
    private static function withPoints(
        ClassMetadata $metadata,
        AccessDirection $direction,
        array $points,
    ): ClassMetadata {
        foreach ($points as $name => $access) {
            $property = $metadata->findProperty($name);

            if ($property !== null) {
                $property = self::withPoint($property, $direction, $access->point, $access->type);
                $metadata = $metadata->withProperty($property);
            }
        }

        return $metadata;
    }

    /**
     * Leaves the property out of the other direction, a point chosen for it
     * and the direction the property does not take part in are errors.
     *
     * @template TArg of object
     * @param \ReflectionClass<object> $class
     * @param ClassMetadata<TArg> $metadata
     * @param array<non-empty-string, AccessMetadata> $getters
     * @param array<non-empty-string, AccessMetadata> $setters
     * @return ClassMetadata<TArg>
     */
    private static function readOnly(
        \ReflectionClass $class,
        \ReflectionProperty $property,
        ClassMetadata $metadata,
        array $getters,
        array $setters,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassMetadata {
        $attributes = $property->getAttributes(MapOnly::class);
        $name = $property->name;

        if ($attributes === [] || $name === '') {
            return $metadata;
        }

        $context = $contexts->createFromReflection($class, $property);
        $instance = AttributeFactory::create($attributes, MapOnly::class, $context, $errors)[0] ?? null;
        $found = self::findMapped($metadata, $name, $context->target, $context->source, $errors);

        if ($instance === null || $found === null) {
            return $metadata;
        }

        $kept = AccessDirection::fromDirection($instance->direction);
        $left = $kept->getOther();
        $chosen = $setters;

        if ($left === AccessDirection::Read) {
            $chosen = $getters;
        }

        if (isset($chosen[$name])) {
            $errors->addError(InvalidAttributeException::becauseDirectionIsLeftOut(
                attribute: $left->getAttribute(),
                only: MapOnly::class,
                property: $context->target,
                source: $context->source,
            ));

            return $metadata;
        }

        if ($kept->findAccess($found) === null) {
            $errors->addError(InvalidAttributeException::becauseDirectionIsMissing(
                attribute: MapOnly::class,
                direction: $kept->getParticiple(),
                property: $context->target,
                source: $context->source,
            ));

            return $metadata;
        }

        return $metadata->withProperty($left->withAccess($found, null));
    }

    /**
     * Returns the property of the name, the property that is not mapped is
     * an error.
     *
     * @param ClassMetadata<object> $metadata
     * @param non-empty-string $name
     * @param string $target property reference: `App\User::$id`
     */
    private static function findMapped(
        ClassMetadata $metadata,
        string $name,
        string $target,
        SourceInfo $source,
        ErrorCollector $errors,
    ): ?PropertyMetadata {
        $property = $metadata->findProperty($name);

        if ($property === null) {
            $errors->addError(UnknownPropertyException::becausePropertyIsNotMapped($target, $source));
        }

        return $property;
    }

    private static function findConfigOnly(ConfigSection $config, ErrorCollector $errors): ?AccessDirection
    {
        if (!$config->has(self::ONLY_KEY)) {
            return null;
        }

        $value = $config->get(self::ONLY_KEY);
        $direction = null;

        if (\is_string($value)) {
            $direction = Direction::tryFrom($value);
        }

        if ($direction !== null) {
            return AccessDirection::fromDirection($direction);
        }

        $context = $config->createContext(self::ONLY_KEY);
        $errors->addError(InvalidOptionValueException::becauseValueIsNotOneOf(
            self::ONLY_KEY,
            [Direction::Normalization->value, Direction::Denormalization->value],
            $context->target,
            $context->source,
        ));

        return null;
    }

    /**
     * Reads the method or, for {@see true}, the hook the key names.
     *
     * @param \ReflectionClass<object> $class
     */
    private function readConfigPoint(
        \ReflectionClass $class,
        mixed $value,
        AccessDirection $direction,
        PropertyMetadata $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $key = $direction->getConfigKey();

        if ($value === true) {
            return $this->readConfigHook($class, $direction, $metadata, $context, $errors);
        }

        if (!\is_string($value) || $value === '') {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotMethodOrTrue(
                $key,
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        if (!$class->hasMethod($value)) {
            $errors->addError(InvalidOptionValueException::becauseMethodDoesNotExist(
                $key,
                $value,
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        $method = $class->getMethod($value);
        $error = self::findMethodError($class, $method, $direction, $context->target, $context->source);

        if ($error !== null) {
            $errors->addError($error);

            return $metadata;
        }

        $type = $this->findMethodType($method, $direction, $context);

        return self::withPoint($metadata, $direction, new MethodAccessPoint($method->name), $type);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function readConfigHook(
        \ReflectionClass $class,
        AccessDirection $direction,
        PropertyMetadata $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $property = null;
        $hook = null;

        if ($class->hasProperty($metadata->name)) {
            $property = $class->getProperty($metadata->name);
            $hook = $direction->findHook($property);
        }

        if ($property === null || $hook === null) {
            $errors->addError(InvalidOptionValueException::becausePropertyHasNoHook(
                $direction->getConfigKey(),
                $direction->getHookName(),
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        $type = $this->findPropertyType($property, $direction->getNativeDirection(), $context);

        return self::withPoint($metadata, $direction, new HookAccessPoint($metadata->name), $type);
    }

    /**
     * Returns the error of the method that cannot be the getter or the
     * setter: a public instance one, a getter without required arguments, a
     * setter taking one value.
     *
     * @param \ReflectionClass<object> $class
     */
    private static function findMethodError(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        AccessDirection $direction,
        string $target,
        SourceInfo $source,
    ): ?InvalidAccessMethodException {
        $reference = self::formatMethod($class, $method);

        if (!$method->isPublic()) {
            return InvalidAccessMethodException::becauseMethodIsNotPublic($reference, $target, $source);
        }

        if ($method->isStatic()) {
            return InvalidAccessMethodException::becauseMethodIsStatic($reference, $target, $source);
        }

        $required = $method->getNumberOfRequiredParameters();

        if ($direction === AccessDirection::Read && $required > 0) {
            return InvalidAccessMethodException::becauseGetterRequiresArguments($reference, $target, $source);
        }

        if ($direction === AccessDirection::Write && ($method->getNumberOfParameters() === 0 || $required > 1)) {
            return InvalidAccessMethodException::becauseSetterDoesNotTakeOneValue($reference, $target, $source);
        }

        return null;
    }

    /**
     * Returns the copy accessing the point in the direction, the type of the
     * replaced access is kept for a point without a native type.
     */
    private static function withPoint(
        PropertyMetadata $metadata,
        AccessDirection $direction,
        AccessPointInterface $point,
        ?TypeMetadata $type,
    ): PropertyMetadata {
        $access = new AccessMetadata($point, $type ?? $direction->findAccess($metadata)?->type);

        return $direction->withAccess($metadata, $access);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return non-empty-string
     */
    private static function formatMethod(\ReflectionClass $class, \ReflectionMethod $method): string
    {
        return $class->name . '::' . $method->name . '()';
    }

    private function findPropertyType(
        \ReflectionProperty $property,
        PropertyAccessDirection $direction,
        SourceContext $context,
    ): ?TypeMetadata {
        try {
            $type = $this->nativeTypeReader->findPropertyType($property, $direction);
        } catch (ReaderExceptionInterface $e) {
            return $context->createInvalidType($e);
        }

        return self::createType($type, $context);
    }

    /**
     * Reads the return type of the getter, the type of the first parameter
     * of the setter.
     */
    private function findMethodType(
        \ReflectionMethod $method,
        AccessDirection $direction,
        SourceContext $context,
    ): ?TypeMetadata {
        $parameter = $method->getParameters()[0] ?? null;

        try {
            if ($direction === AccessDirection::Read) {
                return self::createType($this->nativeTypeReader->findFunctionType($method), $context);
            }

            if ($parameter === null) {
                return null;
            }

            return self::createType($this->nativeTypeReader->findParameterType($parameter), $context);
        } catch (ReaderExceptionInterface $e) {
            return $context->createInvalidType($e);
        }
    }

    private static function createType(?TypeNode $type, SourceContext $context): ?TypeMetadata
    {
        if ($type === null) {
            return null;
        }

        return TypeMetadata::createFromType($type, $context->source);
    }
}
