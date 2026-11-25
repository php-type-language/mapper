<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Access;

use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidAccessMethodException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Reflection\ClassProperties;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Reader\Exception\ReaderExceptionInterface;
use TypeLang\Reader\PropertyAccessDirection;
use TypeLang\Reader\ReaderInterface;
use TypeLang\Type\TypeNode;

/**
 * Accesses of the members declared by the attributes and the config: the
 * backing store or the hook of a property, a method of the form of a getter
 * or a setter. The native type of the access is the type of the field.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class AccessFactory
{
    private readonly SourceContextFactory $contexts;

    public function __construct(
        /**
         * Reader of the native types of the properties, hooks and methods
         */
        private readonly ReaderInterface $nativeTypeReader,
    ) {
        $this->contexts = new SourceContextFactory();
    }

    /**
     * Returns whether the property can be a member, a static one is an
     * error.
     */
    public static function isMappable(
        \ReflectionProperty $property,
        SourceContext $context,
        ErrorCollector $errors,
    ): bool {
        if (!$property->isStatic()) {
            return true;
        }

        $errors->addError(UnknownPropertyException::becausePropertyIsStatic($context->target, $context->source));

        return false;
    }

    /**
     * Returns the access of the property: the backing store or the hook of
     * the direction, {@see null} in case of the property has none of them.
     * The native type has the place of the declaration.
     *
     * @param non-empty-string $name
     * @param non-empty-string|null $key key of the config giving the
     *        access, {@see null} for an attribute
     */
    public function createPropertyAccess(
        \ReflectionProperty $property,
        AccessDirection $direction,
        bool $isHook,
        string $name,
        bool $isNameExplicit,
        SourceContext $context,
        ErrorCollector $errors,
        ?string $key = null,
    ): ?AccessDraft {
        $member = $property->name;
        \assert($member !== '');
        $declaration = $this->contexts->createFromReflection($property->getDeclaringClass(), $property);
        $parent = ClassProperties::findPrivateClass($property);

        if ($isHook) {
            if ($direction->findHook($property) === null) {
                $errors->addError(InvalidOptionValueException::becausePropertyHasNoHook(
                    \sprintf('%s.%s', (string) $key, ConfigAccessReader::HOOK_KEY),
                    $direction->getHookName(),
                    $context->target,
                    $context->source,
                ));

                return null;
            }

            $type = $this->findPropertyType($property, $direction->getNativeDirection(), $declaration);

            return new AccessDraft(new HookAccessMetadata($member, $parent), $name, $isNameExplicit, $type);
        }

        if (PropertyHooks::isVirtual($property)) {
            $errors->addError(InvalidOptionValueException::becausePropertyHasNoSlot(
                (string) $key,
                $context->target,
                $context->source,
            ));

            return null;
        }

        $type = $this->findPropertyType($property, PropertyAccessDirection::Read, $declaration);

        return new AccessDraft(new SlotAccessMetadata($member, $parent), $name, $isNameExplicit, $type);
    }

    /**
     * Returns the method as the access of the direction, a method that
     * cannot be the getter or the setter is an error.
     *
     * @param \ReflectionClass<object> $class
     * @param non-empty-string $name
     */
    public function createMethodAccess(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        AccessDirection $direction,
        string $name,
        bool $isNameExplicit,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?AccessDraft {
        $error = self::findMethodError($class, $method, $direction, $context->source);

        if ($error !== null) {
            $errors->addError($error);

            return null;
        }

        $declaration = $this->contexts->createFromMethod($method->getDeclaringClass(), $method);
        $type = $this->findMethodType($method, $direction, $declaration);

        return new AccessDraft(new MethodAccessMetadata($method->name), $name, $isNameExplicit, $type);
    }

    /**
     * Returns the member of the method, {@see null} in case of it has no
     * direction. A setter with an optional parameter makes the field
     * optional.
     */
    public static function createMethodMember(
        \ReflectionMethod $method,
        ?AccessDraft $read,
        ?AccessDraft $write,
    ): ?MemberDraft {
        if ($read === null && $write === null) {
            return null;
        }

        $member = new MemberDraft($method->name . '()', $read, $write);
        $parameter = $method->getParameters()[0] ?? null;

        if ($write === null || $parameter === null) {
            return $member;
        }

        return $member->withOptional($parameter->isOptional());
    }

    public static function isGetter(\ReflectionMethod $method): bool
    {
        return $method->getNumberOfRequiredParameters() === 0;
    }

    public static function isSetter(\ReflectionMethod $method): bool
    {
        return $method->getNumberOfParameters() > 0
            && $method->getNumberOfRequiredParameters() <= 1
            && !$method->getParameters()[0]->isVariadic();
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return non-empty-string
     */
    public static function formatMethod(\ReflectionClass $class, \ReflectionMethod $method): string
    {
        return $class->name . '::' . $method->name . '()';
    }

    /**
     * Returns the error of the method that cannot be the getter or the
     * setter: a public instance one other than the constructor and the magic
     * methods, a getter without required arguments returning a value, a
     * setter taking one value.
     *
     * @param \ReflectionClass<object> $class
     */
    private static function findMethodError(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        AccessDirection $direction,
        SourceInfo $source,
    ): ?InvalidAccessMethodException {
        $reference = self::formatMethod($class, $method);

        if ($method->isConstructor() || $method->isDestructor() || \str_starts_with($method->name, '__')) {
            return InvalidAccessMethodException::becauseMethodIsMagic($reference, $source);
        }

        if (!$method->isPublic()) {
            return InvalidAccessMethodException::becauseMethodIsNotPublic($reference, $source);
        }

        if ($method->isStatic()) {
            return InvalidAccessMethodException::becauseMethodIsStatic($reference, $source);
        }

        if ($direction === AccessDirection::Read && !self::isGetter($method)) {
            return InvalidAccessMethodException::becauseGetterRequiresArguments($reference, $source);
        }

        $returns = $method->getReturnType();

        if ($direction === AccessDirection::Read
            && $returns instanceof \ReflectionNamedType
            && \in_array($returns->getName(), ['void', 'never'], true)) {
            /** @var 'void'|'never' */
            $name = $returns->getName();

            return InvalidAccessMethodException::becauseGetterReturnsNothing($reference, $name, $source);
        }

        if ($direction === AccessDirection::Write && !self::isSetter($method)) {
            return InvalidAccessMethodException::becauseSetterDoesNotTakeOneValue($reference, $source);
        }

        return null;
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
