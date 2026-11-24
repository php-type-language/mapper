<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidAttributeException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\NamingStrategy;
use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;
use TypeLang\Mapper\Mapping\Reader\Access\AccessDirection;
use TypeLang\Mapper\Mapping\Reader\Access\AccessFactory;
use TypeLang\Mapper\Mapping\Reader\Access\AttributeAccessReader;
use TypeLang\Mapper\Mapping\Reader\Option\Options;
use TypeLang\Mapper\Mapping\Reader\Option\SkipOption;
use TypeLang\Mapper\Mapping\Reader\Reflection\ClassProperties;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Reader\ReaderInterface;
use TypeLang\Reader\ReflectionReader;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given members and of the class with the
 * attributes. {@see MapGetter} and {@see MapSetter} declare the members, see
 * {@see self::readAccess()}. The helpers of a property without them change
 * one value of its fields: {@see MapName}, {@see MapType}, {@see MapAlias},
 * {@see OnTypeError}, {@see OnUndefinedError}. {@see SkipWhenNull},
 * {@see SkipWhenEmpty} and {@see SkipWhen} are given where the read is
 * declared: on a property, a getter or a `get` hook. Of a class it reads
 * {@see DiscriminatorMap}, {@see OnTypeError}, {@see NormalizeAsArray},
 * {@see NamingStrategy}, of a case {@see MapAlias}. Names in the types are
 * resolved by the file declaring the member or the class.
 *
 * A helper next to an attribute of the access, an attribute of a property
 * that is not mapped, an attribute of a direction the attributes of the
 * access do not declare and an attribute of the mapper whose declaration
 * does not allow a method on a method or a hook are errors. A helper of a
 * property without them is read in both directions: a direction another
 * source leaves out takes no value of it.
 */
final class AttributeMetadataReader implements AccessSourceReaderInterface
{
    /**
     * Prefix of the attributes of the mapper checked on the methods and the
     * hooks
     */
    private const MAPPER_NAMESPACE = 'TypeLang\\Mapper\\Mapping\\';

    private readonly SourceContextFactory $contexts;

    private readonly Options $options;

    private readonly AttributeAccessReader $access;

    /**
     * Helpers of a property and their directions, {@see null} for both
     *
     * @var array<class-string, AccessDirection|null>
     */
    private readonly array $helpers;

    /**
     * @param TypeParserInterface<TypeNode, string> $parser parser of the
     *        types of the attributes
     * @param ExpressionLanguage|null $expressions parser of the expressions of
     *        {@see SkipWhen}, created on the first one in case of {@see null}
     * @param ReaderInterface $nativeTypeReader reader of the native types of
     *        the properties, hooks and methods
     */
    public function __construct(
        private readonly TypeParserInterface $parser = new TypeParser(),
        ?ExpressionLanguage $expressions = null,
        ReaderInterface $nativeTypeReader = new ReflectionReader(),
    ) {
        $this->contexts = new SourceContextFactory();
        $this->options = Options::createDefault($parser, $expressions);
        $this->access = new AttributeAccessReader(new AccessFactory($nativeTypeReader));

        $helpers = [];

        foreach ($this->options->properties as $option) {
            if ($option !== $this->options->skip) {
                $helpers = [...$helpers, ...$option->getPropertyAttributes()];
            }
        }

        $this->helpers = $helpers;
    }

    /**
     * @throws InvalidMetadataException in case of an attribute is wrong,
     *         belongs to a property that is not given or is not allowed where
     *         it is given
     * @throws MissingDependencyException in case of {@see SkipWhen} is
     *         given, but the `symfony/expression-language` package is not
     *         installed
     */
    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        $metadata ??= new ClassDraft($class->getName());
        $errors = new ErrorCollector();

        foreach (ClassProperties::getProperties($class) as $property) {
            $metadata = $this->readProperty($class, $property, $metadata, $errors);
        }

        foreach ($this->options->classes as $option) {
            $metadata = $option->readClassAttributes($class, $metadata, $this->contexts, $errors);
        }

        $metadata = $this->access->readValues($class, $metadata, $this->parser, $this->contexts, $errors);
        $metadata = $this->readMethodConditions($class, $metadata, $errors);
        $metadata = $this->readHookConditions($class, $metadata, $errors);

        self::addTargetErrors($class, $errors);

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * Reads the members of {@see MapGetter} and {@see MapSetter}.
     *
     * @throws InvalidMetadataException in case of an attribute is wrong or
     *         is not allowed where it is given
     */
    public function readAccess(\ReflectionClass $class, ClassDraft $metadata): ClassDraft
    {
        $errors = new ErrorCollector();

        $metadata = $this->access->readAccess($class, $metadata, $this->contexts, $errors);

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     * @throws MissingDependencyException in case of {@see SkipWhen} is
     *         given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readProperty(
        \ReflectionClass $class,
        \ReflectionProperty $reflection,
        ClassDraft $metadata,
        ErrorCollector $errors,
    ): ClassDraft {
        $options = [];

        foreach ($this->options->properties as $option) {
            if (self::getGivenAttributes($reflection, $option->getPropertyAttributes()) !== []) {
                $options[] = $option;
            }
        }

        $name = $reflection->name;

        if ($name === '' || $options === []) {
            return $metadata;
        }

        $context = $this->contexts->createFromReflection($class, $reflection);

        if (AttributeAccessReader::hasAttributes($reflection)) {
            return $this->readDeclaredProperty($class, $reflection, $metadata, $context, $errors);
        }

        $key = ClassProperties::getKey($class, $reflection);
        $member = null;

        if ($key !== '') {
            $member = $metadata->findMember($key);
        }

        if ($member === null) {
            $this->addNotMappedErrors($reflection, $context, $errors);

            return $metadata;
        }

        foreach ($options as $option) {
            $member = $option->readPropertyAttributes($reflection, $member, $context, $errors);
        }

        return $metadata->withMember($member);
    }

    /**
     * The helpers of a property with an attribute of the access are errors,
     * the skip conditions are allowed next to {@see MapGetter} of the
     * property.
     *
     * @template TArg of object
     * @param \ReflectionClass<object> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    private function readDeclaredProperty(
        \ReflectionClass $class,
        \ReflectionProperty $reflection,
        ClassDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): ClassDraft {
        $access = AttributeAccessReader::getAttributeNames($reflection);

        foreach (self::getGivenAttributes($reflection, $this->helpers) as $attribute => $direction) {
            if ($direction !== null && !\in_array($direction->getAttribute(), $access, true)) {
                self::addDirectionError($attribute, $direction, $context, $errors);

                continue;
            }

            $errors->addError(InvalidAttributeException::becauseAttributeIsNextToAccess(
                attribute: $attribute,
                access: $access,
                property: $context->target,
                source: $context->source,
            ));
        }

        $skip = \array_keys(self::getGivenAttributes($reflection, $this->options->skip->getPropertyAttributes()));
        $key = ClassProperties::getKey($class, $reflection);
        $member = null;

        if ($key !== '') {
            $member = $metadata->findMember($key);
        }

        if ($skip === [] || $member === null) {
            return $metadata;
        }

        if (AttributeAccessReader::isReadOnProperty($reflection)) {
            return $metadata->withMember($this->options->skip->readAttributes($reflection, $member, $context, $errors));
        }

        foreach ($skip as $attribute) {
            if ($member->read?->access instanceof HookAccessMetadata) {
                $errors->addError(InvalidAttributeException::becauseReadIsOnHook(
                    attribute: $attribute,
                    property: $context->target,
                    source: $context->source,
                ));

                continue;
            }

            self::addDirectionError($attribute, AccessDirection::Read, $context, $errors);
        }

        return $metadata;
    }

    /**
     * Reads the skip conditions of the getters.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    private function readMethodConditions(\ReflectionClass $class, ClassDraft $metadata, ErrorCollector $errors): ClassDraft
    {
        foreach ($class->getMethods() as $method) {
            if (SkipOption::getAttributes($method) === []) {
                continue;
            }

            $context = $this->contexts->createFromMethod($class, $method);
            $member = $metadata->findMember($method->name . '()');

            if ($method->getAttributes(MapGetter::class) === []) {
                self::addGetterErrors($method, $context, $errors);

                continue;
            }

            if ($member?->read === null) {
                self::addNotReadErrors($method, $context, $errors);

                continue;
            }

            $metadata = $metadata->withMember($this->options->skip->readAttributes($method, $member, $context, $errors));
        }

        return $metadata;
    }

    /**
     * Reads the skip conditions of the `get` hooks, of a `set` hook they are
     * errors.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    private function readHookConditions(\ReflectionClass $class, ClassDraft $metadata, ErrorCollector $errors): ClassDraft
    {
        foreach (ClassProperties::getProperties($class) as $property) {
            $name = $property->name;
            $set = PropertyHooks::findSet($property);
            $get = PropertyHooks::findGet($property);

            if ($name === '') {
                continue;
            }

            $context = $this->contexts->createFromReflection($class, $property);

            if ($set !== null && SkipOption::getAttributes($set) !== []) {
                self::addNotReadErrors($set, $context, $errors);
            }

            if ($get === null || SkipOption::getAttributes($get) === []) {
                continue;
            }

            $key = ClassProperties::getKey($class, $property);
            $member = null;

            if ($key !== '') {
                $member = $metadata->findMember($key);
            }

            $place = new SourceContext($context->target, SourceInfo::createFromReflection($get));

            if ($get->getAttributes(MapGetter::class) === []) {
                self::addGetterErrors($get, $place, $errors);

                continue;
            }

            if (!$member?->read?->access instanceof HookAccessMetadata) {
                self::addNotReadErrors($get, $place, $errors);

                continue;
            }

            $metadata = $metadata->withMember($this->options->skip->readAttributes($get, $member, $place, $errors));
        }

        return $metadata;
    }

    /**
     * Reports the attributes of the mapper whose declaration does not allow
     * the place they are given on: the class, a property, a constant or a
     * case, a method or a hook.
     *
     * @param \ReflectionClass<object> $class
     */
    private static function addTargetErrors(\ReflectionClass $class, ErrorCollector $errors): void
    {
        $source = SourceInfo::createFromReflection($class);

        foreach (self::getMisplacedAttributes($class, \Attribute::TARGET_CLASS) as $name) {
            $errors->addError(InvalidAttributeException::becauseAttributeIsNotAllowed(
                attribute: $name,
                place: 'a class',
                target: $class->name,
                source: $source,
            ));
        }

        foreach ($class->getReflectionConstants() as $constant) {
            foreach (self::getMisplacedAttributes($constant, \Attribute::TARGET_CLASS_CONSTANT) as $name) {
                $errors->addError(InvalidAttributeException::becauseAttributeIsNotAllowed(
                    attribute: $name,
                    place: 'a constant or a case',
                    target: $class->name . '::' . $constant->name,
                    source: $source,
                ));
            }
        }

        foreach ($class->getMethods() as $method) {
            self::addMethodTargetErrors($method, AccessFactory::formatMethod($class, $method), $errors);
        }

        foreach (ClassProperties::getProperties($class) as $property) {
            $target = $class->name . '::$' . $property->name;

            foreach (self::getMisplacedAttributes($property, \Attribute::TARGET_PROPERTY) as $name) {
                $errors->addError(InvalidAttributeException::becauseAttributeIsNotAllowed(
                    attribute: $name,
                    place: 'a property',
                    target: $target,
                    source: SourceInfo::createFromReflection($property->getDeclaringClass()),
                ));
            }

            foreach ([PropertyHooks::findGet($property), PropertyHooks::findSet($property)] as $hook) {
                if ($hook !== null) {
                    self::addMethodTargetErrors($hook, $target, $errors);
                }
            }
        }
    }

    /**
     * @param non-empty-string $target
     */
    private static function addMethodTargetErrors(\ReflectionMethod $method, string $target, ErrorCollector $errors): void
    {
        foreach (self::getMisplacedAttributes($method, \Attribute::TARGET_METHOD) as $name) {
            $errors->addError(InvalidAttributeException::becauseAttributeIsOnMethod(
                attribute: $name,
                method: $target,
                source: SourceInfo::createFromReflection($method),
            ));
        }
    }

    /**
     * Returns the names of the attributes of the mapper the declaration of
     * which does not allow the target.
     *
     * @param \ReflectionClass<object>|\ReflectionClassConstant|\ReflectionProperty|\ReflectionMethod $reflection
     * @param int-mask-of<\Attribute::TARGET_*> $target
     * @return list<class-string>
     */
    private static function getMisplacedAttributes(
        \ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionMethod $reflection,
        int $target,
    ): array {
        $result = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $name = $attribute->getName();

            if (\str_starts_with($name, self::MAPPER_NAMESPACE) && !AttributeFactory::isAllowedOn($name, $target)) {
                $result[] = $name;
            }
        }

        return $result;
    }

    private static function addGetterErrors(
        \ReflectionMethod $method,
        SourceContext $context,
        ErrorCollector $errors,
    ): void {
        foreach (SkipOption::getAttributes($method) as $attribute) {
            $errors->addError(InvalidAttributeException::becauseGetterIsMissing(
                attribute: $attribute->getName(),
                target: $context->target,
                source: SourceInfo::createFromReflection($method),
            ));
        }
    }

    private static function addNotReadErrors(\ReflectionMethod $method, SourceContext $context, ErrorCollector $errors): void
    {
        foreach (SkipOption::getAttributes($method) as $attribute) {
            $errors->addError(InvalidAttributeException::becauseDirectionIsMissing(
                attribute: $attribute->getName(),
                direction: AccessDirection::Read->getParticiple(),
                target: $context->target,
                source: SourceInfo::createFromReflection($method),
            ));
        }
    }

    /**
     * Reports the attributes of a property that is not mapped: a virtual
     * property is mapped by the attributes of its hooks.
     */
    private function addNotMappedErrors(
        \ReflectionProperty $reflection,
        SourceContext $context,
        ErrorCollector $errors,
    ): void {
        if ($reflection->isStatic()) {
            $errors->addError(UnknownPropertyException::becausePropertyIsStatic($context->target, $context->source));

            return;
        }

        if (!PropertyHooks::isVirtual($reflection)) {
            $errors->addError(UnknownPropertyException::becausePropertyWithAttributeIsNotMapped(
                $context->target,
                $context->source,
            ));

            return;
        }

        $attributes = [...$this->helpers, ...$this->options->skip->getPropertyAttributes()];

        foreach (\array_keys(self::getGivenAttributes($reflection, $attributes)) as $attribute) {
            $errors->addError(InvalidAttributeException::becausePropertyIsVirtual(
                attribute: $attribute,
                property: $context->target,
                source: $context->source,
            ));
        }
    }

    private static function addDirectionError(
        string $attribute,
        AccessDirection $direction,
        SourceContext $context,
        ErrorCollector $errors,
    ): void {
        $errors->addError(InvalidAttributeException::becauseDirectionIsMissing(
            attribute: $attribute,
            direction: $direction->getParticiple(),
            target: $context->target,
            source: $context->source,
        ));
    }

    /**
     * Returns the attributes among the given ones the property has.
     *
     * @param array<class-string, AccessDirection|null> $attributes
     * @return array<class-string, AccessDirection|null>
     */
    private static function getGivenAttributes(\ReflectionProperty $reflection, array $attributes): array
    {
        $result = [];

        foreach ($attributes as $attribute => $direction) {
            if ($reflection->getAttributes($attribute) !== []) {
                $result[$attribute] = $direction;
            }
        }

        return $result;
    }
}
