<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Metadata\Access\AccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\PropertyContext;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\PhpDoc\DocBlock\Tag\ParamTag\ParamTag;
use TypeLang\PhpDoc\DocBlock\Tag\ReturnTag\ReturnTag;
use TypeLang\PhpDoc\DocBlock\Tag\TypedTag;
use TypeLang\PhpDoc\DocBlock\Tag\VarTag\VarTag;
use TypeLang\PhpDoc\DocBlockParser;

/**
 * Overrides types of the given members with types from docblocks and adds
 * no members. The docblock is chosen by the access: the property for its
 * backing store, the hook and then the property for a hook, the method for
 * a method.
 *
 * Note: Requires the `type-lang/phpdoc` package.
 */
final class PhpDocMetadataReader implements OptionalMetadataReaderInterface
{
    private readonly DocBlockParser $parser;

    private readonly SourceContextFactory $contexts;

    /**
     * @param DocBlockParser|null $parser parser of the docblocks, the
     *        standard tags and the tags of the mapper in case of {@see null}
     * @throws MissingDependencyException in case of the `type-lang/phpdoc`
     *         package is not installed
     */
    public function __construct(
        ?DocBlockParser $parser = null,
    ) {
        if (!self::isAvailable()) {
            throw MissingDependencyException::becausePackageIsNotInstalled('type-lang/phpdoc', 'PHPDoc tags');
        }

        $this->parser = $parser ?? self::createDefaultDocBlockParser();
        $this->contexts = new SourceContextFactory();
    }

    public static function isAvailable(): bool
    {
        return \class_exists(DocBlockParser::class);
    }

    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        $metadata ??= new ClassDraft($class->getName());

        foreach ($metadata->members as $member) {
            $read = null;

            if ($member->read !== null) {
                $read = $this->findReadType($class, $member->read->access);
            }

            if ($read !== null) {
                $member = $member->withReadType($read);
            }

            // both directions of one backing store have one type
            $write = $read;

            if ($member->write !== null && !self::isSameStorage($member->read, $member->write)) {
                $write = $this->findWriteType($class, $member->write->access);
            }

            if ($write !== null) {
                $member = $member->withWriteType($write);
            }

            $metadata = $metadata->withMember($member);
        }

        return $metadata;
    }

    /**
     * Reads the type of the backing store from the property, of the `get`
     * hook from its `@return` and then from the property, of the getter from
     * its `@return`.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findReadType(\ReflectionClass $class, AccessMetadata $access): ?TypeMetadata
    {
        if ($access instanceof MethodAccessMetadata) {
            return $this->findMethodType($class, $access, ReturnTag::class, null);
        }

        $context = $this->findContext($class, $access);

        if ($context === null) {
            return null;
        }

        if ($access instanceof HookAccessMetadata) {
            return self::findGetHookType($context) ?? self::findPropertyType($context);
        }

        return self::findPropertyType($context);
    }

    /**
     * Reads the type of the backing store from the property, of the `set`
     * hook from its `@param` and then from the property in case of the
     * parameter has the native type of the property, of the setter from its
     * `@param`.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findWriteType(\ReflectionClass $class, AccessMetadata $access): ?TypeMetadata
    {
        if ($access instanceof MethodAccessMetadata) {
            $parameter = self::findMethod($class, $access)?->getParameters()[0] ?? null;

            if ($parameter === null) {
                return null;
            }

            return $this->findMethodType($class, $access, ParamTag::class, $parameter->name);
        }

        $context = $this->findContext($class, $access);

        if ($context === null) {
            return null;
        }

        $setter = PropertyHooks::findSet($context->reflection);

        if ($access instanceof HookAccessMetadata && $setter !== null) {
            return self::findSetHookType($context, $setter);
        }

        return self::findPropertyType($context);
    }

    /**
     * Reads the tag of the method naming the given variable or none, its
     * type is resolved by the file declaring the method.
     *
     * @param \ReflectionClass<object> $class
     * @param class-string<TypedTag> $tag
     */
    private function findMethodType(
        \ReflectionClass $class,
        MethodAccessMetadata $access,
        string $tag,
        ?string $variable,
    ): ?TypeMetadata {
        $method = self::findMethod($class, $access);
        $docComment = $method?->getDocComment() ?? false;

        if ($method === null || $docComment === false) {
            return null;
        }

        foreach ($this->parser->parse($docComment) as $found) {
            if (!$found instanceof $tag) {
                continue;
            }

            if ($variable !== null && (!$found instanceof ParamTag || $found->variable !== $variable)) {
                continue;
            }

            return $this->contexts->createFromMethod($class, $method)->resolveType(clone $found->type);
        }

        return null;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private static function findMethod(\ReflectionClass $class, MethodAccessMetadata $access): ?\ReflectionMethod
    {
        if (!$class->hasMethod($access->method)) {
            return null;
        }

        return $class->getMethod($access->method);
    }

    private static function isSameStorage(?AccessDraft $read, ?AccessDraft $write): bool
    {
        return $read?->access instanceof SlotAccessMetadata
            && $write?->access instanceof SlotAccessMetadata
            && $read->access->property === $write->access->property;
    }

    /**
     * Returns the context of the property of the backing store or the hook,
     * {@see null} for another access or in case of the class has no such
     * property.
     *
     * @param \ReflectionClass<object> $class
     */
    private function findContext(\ReflectionClass $class, AccessMetadata $access): ?PropertyContext
    {
        if (!$access instanceof SlotAccessMetadata && !$access instanceof HookAccessMetadata) {
            return null;
        }

        $owner = $class;

        if ($access->class !== null) {
            $owner = new \ReflectionClass($access->class);
        }

        if (!$owner->hasProperty($access->property)) {
            return null;
        }

        return PropertyContext::create(
            class: $class,
            reflection: $owner->getProperty($access->property),
            parser: $this->parser,
            contexts: $this->contexts,
        );
    }

    /**
     * Reads `@var` of the property, then `@param` of the constructor for a
     * promoted property.
     */
    private static function findPropertyType(PropertyContext $context): ?TypeMetadata
    {
        return self::findVarType($context) ?? self::findPromotedParamType($context);
    }

    private static function findVarType(PropertyContext $context): ?TypeMetadata
    {
        return $context->createType($context->findTag(
            docComment: $context->reflection->getDocComment(),
            class: VarTag::class,
            variable: $context->reflection->name,
        ));
    }

    private static function findPromotedParamType(PropertyContext $context): ?TypeMetadata
    {
        $constructor = $context->scope->getConstructor();

        if ($constructor === null || !$context->reflection->isPromoted()) {
            return null;
        }

        return $context->createType($context->findTag(
            docComment: $constructor->getDocComment(),
            class: ParamTag::class,
            variable: $context->reflection->name,
        ));
    }

    private static function findGetHookType(PropertyContext $context): ?TypeMetadata
    {
        $get = PropertyHooks::findGet($context->reflection);

        if ($get === null) {
            return null;
        }

        return $context->createType($context->findTag(
            docComment: $get->getDocComment(),
            class: ReturnTag::class,
            variable: $context->reflection->name,
        ));
    }

    /**
     * Returns {@see true} in case of the `set` hook takes another native type
     * than the property. Its `@param` is read before.
     */
    private static function hasOwnWriteType(\ReflectionProperty $property, \ReflectionMethod $hook): bool
    {
        $parameter = $hook->getParameters()[0] ?? null;

        return (string) $parameter?->getType() !== (string) $property->getType();
    }

    /**
     * Reads `@param` of the `set` hook, then the property in case of the
     * hook takes the native type of the property.
     */
    private static function findSetHookType(PropertyContext $context, \ReflectionMethod $hook): ?TypeMetadata
    {
        $parameter = $hook->getParameters()[0]->name ?? '';
        $type = null;

        if ($parameter !== '') {
            $type = $context->createType($context->findTag(
                docComment: $hook->getDocComment(),
                class: ParamTag::class,
                variable: $parameter,
            ));
        }

        if ($type !== null || self::hasOwnWriteType($context->reflection, $hook)) {
            return $type;
        }

        return self::findPropertyType($context);
    }

    private static function createDefaultDocBlockParser(): DocBlockParser
    {
        return new DocBlockParser([
            MapperPlatform::createWithDefaultTagNames(),
        ]);
    }
}
