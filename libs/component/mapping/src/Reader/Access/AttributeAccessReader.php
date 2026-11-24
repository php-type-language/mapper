<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Access;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Reader\Reflection\ClassProperties;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\DefaultValueFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Members declared by {@see MapGetter} and {@see MapSetter}. The position
 * chooses the access: a property is accessed through its backing store, a
 * hook through the hook, a method by calling it. A private property of a
 * parent is a member of its own.
 *
 * The members and their accesses are read in the access phase, the
 * arguments of the attributes with the values of the other readers, in the
 * order of their priority.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class AttributeAccessReader
{
    public function __construct(
        private readonly AccessFactory $factory,
    ) {}

    /**
     * Returns whether the property or one of its hooks has an attribute of
     * an access.
     */
    public static function hasAttributes(\ReflectionProperty $property): bool
    {
        return self::getAttributeNames($property) !== [];
    }

    /**
     * Returns whether the property declares its read on the property
     * itself, not on the hook.
     */
    public static function isReadOnProperty(\ReflectionProperty $property): bool
    {
        return $property->getAttributes(MapGetter::class) !== [];
    }

    /**
     * Returns the attributes of the access given on the property and its
     * hooks, a hook of the other direction included.
     *
     * @return list<class-string<MapGetter|MapSetter>>
     */
    public static function getAttributeNames(\ReflectionProperty $property): array
    {
        $result = [];

        foreach (AccessDirection::cases() as $direction) {
            $attribute = $direction->getAttribute();

            foreach ([$property, PropertyHooks::findGet($property), PropertyHooks::findSet($property)] as $place) {
                if (($place?->getAttributes($attribute) ?? []) !== []) {
                    $result[] = $attribute;

                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Reads the members declared by the attributes of the properties, their
     * hooks and the methods.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    public function readAccess(
        \ReflectionClass $class,
        ClassDraft $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassDraft {
        foreach (ClassProperties::getProperties($class) as $property) {
            if ($property->name === '' || !self::hasAttributes($property)) {
                continue;
            }

            $member = $this->readProperty($class, $property, $contexts, $errors);

            if ($member !== null) {
                $metadata = $metadata->withMember($member);
            }
        }

        foreach ($class->getMethods() as $method) {
            $member = $this->readMethod($class, $method, $errors);

            if ($member !== null) {
                $metadata = $metadata->withMember($member);
            }
        }

        return $metadata;
    }

    /**
     * Reads the arguments of the attributes of the declared members: the
     * names, the types and the options of the denormalization. Errors of
     * creating the attributes are reported by {@see self::readAccess()}.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @param TypeParserInterface<TypeNode, string> $parser
     * @return ClassDraft<TArg>
     */
    public function readValues(
        \ReflectionClass $class,
        ClassDraft $metadata,
        TypeParserInterface $parser,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassDraft {
        foreach (ClassProperties::getProperties($class) as $property) {
            $key = ClassProperties::getKey($class, $property);
            $member = null;

            if ($key !== '' && self::hasAttributes($property)) {
                $member = $metadata->findMember($key);
            }

            if ($member === null) {
                continue;
            }

            $context = $contexts->createFromReflection($class, $property);
            $read = self::getPropertyAttributes($property, AccessDirection::Read);
            $write = self::getPropertyAttributes($property, AccessDirection::Write);
            $arguments = new AccessArguments(
                getter: self::findInstance($read, MapGetter::class, $context),
                setter: self::findInstance($write, MapSetter::class, $context),
            );

            $metadata = $metadata->withMember($arguments->apply($member, $parser, $context, $errors));
        }

        foreach ($class->getMethods() as $method) {
            $member = $metadata->findMember($method->name . '()');

            if ($member === null) {
                continue;
            }

            $context = $contexts->createFromMethod($class, $method);
            $arguments = new AccessArguments(
                getter: self::findInstance($method->getAttributes(MapGetter::class), MapGetter::class, $context),
                setter: self::findInstance($method->getAttributes(MapSetter::class), MapSetter::class, $context),
            );

            $metadata = $metadata->withMember($arguments->apply($member, $parser, $context, $errors));
        }

        return $metadata;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function readProperty(
        \ReflectionClass $class,
        \ReflectionProperty $property,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ?MemberDraft {
        $name = $property->name;
        $key = ClassProperties::getKey($class, $property);
        $context = $contexts->createFromReflection($class, $property);

        if ($name === '' || $key === '' || !AccessFactory::isMappable($property, $context, $errors)) {
            return null;
        }

        $accesses = [];
        $isValid = true;

        foreach (AccessDirection::cases() as $direction) {
            $attribute = $direction->getAttribute();
            $other = $direction->getOther();
            $wrong = $other->findHook($property);

            if ($wrong !== null && $wrong->getAttributes($attribute) !== []) {
                $isValid = false;
                $errors->addError(InvalidAttributeException::becauseAttributeIsOnWrongHook(
                    attribute: $attribute,
                    hook: $other->getHookName(),
                    property: $context->target,
                    source: SourceInfo::createFromReflection($wrong),
                ));
            }

            $onProperty = $property->getAttributes($attribute);
            $hook = $direction->findHook($property);
            $onHook = $hook?->getAttributes($attribute) ?? [];

            if ($onProperty !== [] && $onHook !== []) {
                $isValid = false;
                $errors->addError(InvalidAttributeException::becauseAccessIsDuplicated(
                    attribute: $attribute,
                    property: $context->target,
                    source: $context->source,
                ));

                continue;
            }

            $place = $context;
            $attributes = $onProperty;

            if ($hook !== null && $onHook !== []) {
                $place = new SourceContext($context->target, SourceInfo::createFromReflection($hook));
                $attributes = $onHook;
            }

            if ($attributes === []) {
                continue;
            }

            if (AttributeFactory::create($attributes, $attribute, $place, $errors) === []) {
                $isValid = false;

                continue;
            }

            if ($onProperty !== [] && PropertyHooks::isVirtual($property)) {
                $isValid = false;
                $errors->addError(InvalidAttributeException::becausePropertyHasNoSlot(
                    attribute: $attribute,
                    property: $context->target,
                    source: $context->source,
                ));

                continue;
            }

            $accesses[$direction->name] = $this->factory->createPropertyAccess(
                property: $property,
                direction: $direction,
                isHook: $onProperty === [],
                name: $name,
                isNameExplicit: false,
                context: $place,
                errors: $errors,
            );
        }

        if (!$isValid) {
            return null;
        }

        $member = new MemberDraft(
            key: $key,
            read: $accesses[AccessDirection::Read->name] ?? null,
            write: $accesses[AccessDirection::Write->name] ?? null,
        );

        return DefaultValueFactory::withPropertyDefault($member, $property, $context, $errors);
    }

    /**
     * The name of the field of a method is required: its value is read with
     * the other arguments.
     *
     * @param \ReflectionClass<object> $class
     */
    private function readMethod(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        ErrorCollector $errors,
    ): ?MemberDraft {
        $target = AccessFactory::formatMethod($class, $method);
        $context = new SourceContext($target, SourceInfo::createFromReflection($method));
        $accesses = [];

        foreach (AccessDirection::cases() as $direction) {
            $attribute = $direction->getAttribute();
            $attributes = $method->getAttributes($attribute);

            if ($attributes === []) {
                continue;
            }

            $instance = AttributeFactory::create($attributes, $attribute, $context, $errors)[0] ?? null;

            if ($instance === null) {
                continue;
            }

            if ($instance->name === null) {
                $errors->addError(InvalidAttributeException::becauseMethodRequiresName(
                    attribute: $attribute,
                    method: $target,
                    source: $context->source,
                ));

                continue;
            }

            $accesses[$direction->name] = $this->factory->createMethodAccess(
                class: $class,
                method: $method,
                direction: $direction,
                name: $method->name,
                isNameExplicit: true,
                context: $context,
                errors: $errors,
            );
        }

        return AccessFactory::createMethodMember(
            $method,
            $accesses[AccessDirection::Read->name] ?? null,
            $accesses[AccessDirection::Write->name] ?? null,
        );
    }

    /**
     * Returns the attributes of the direction given on the property or, in
     * case of none, on its hook.
     *
     * @return list<\ReflectionAttribute<object>>
     */
    private static function getPropertyAttributes(\ReflectionProperty $property, AccessDirection $direction): array
    {
        $attribute = $direction->getAttribute();
        $attributes = $property->getAttributes($attribute);

        if ($attributes === []) {
            return $direction->findHook($property)?->getAttributes($attribute) ?? [];
        }

        return $attributes;
    }

    /**
     * Returns the first instance of the attribute, its errors are reported
     * by {@see self::readAccess()}.
     *
     * @template TArg of object
     * @param list<\ReflectionAttribute<object>> $attributes
     * @param class-string<TArg> $class
     * @return TArg|null
     */
    private static function findInstance(array $attributes, string $class, SourceContext $context): ?object
    {
        return AttributeFactory::create($attributes, $class, $context, new ErrorCollector())[0] ?? null;
    }
}
