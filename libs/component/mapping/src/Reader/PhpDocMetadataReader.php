<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\PropertyContext;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\PhpDoc\DocBlock\Tag\ParamTag\ParamTag;
use TypeLang\PhpDoc\DocBlock\Tag\ReturnTag\ReturnTag;
use TypeLang\PhpDoc\DocBlock\Tag\VarTag\VarTag;
use TypeLang\PhpDoc\DocBlockParser;

/**
 * Overrides types of the given properties with types from docblocks and
 * adds no properties.
 *
 * Note: Requires the `type-lang/phpdoc` package.
 */
final class PhpDocMetadataReader implements OptionalMetadataReaderInterface
{
    private readonly DocBlockParser $parser;

    private readonly SourceContextFactory $contexts;

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

    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        $metadata ??= new ClassMetadata($class->getName());

        foreach ($metadata->properties as $name => $property) {
            if (!$class->hasProperty($name)) {
                continue;
            }

            $context = PropertyContext::create(
                class: $class,
                reflection: $class->getProperty($name),
                parser: $this->parser,
                contexts: $this->contexts,
            );

            $metadata = $metadata->withProperty($this->readProperty($context, $property));
        }

        return $metadata;
    }

    /**
     * Reads the type from `@return` of the `get` hook and `@param` of the
     * `set` hook, then from `@var` of the property, then from `@param` of the
     * constructor for a promoted property. A property with the `set` hook
     * takes the write type from this hook only.
     */
    private function readProperty(PropertyContext $context, PropertyMetadata $property): PropertyMetadata
    {
        $set = PropertyHooks::findSet($context->reflection);
        $read = self::findGetHookType($context);
        $write = null;

        if ($set !== null) {
            $write = self::findSetHookType($context, $set);
        }

        if ($read === null || $set === null) {
            $type = self::findVarType($context) ?? self::findPromotedParamType($context);

            $read ??= $type;

            if ($set === null) {
                $write = $type;
            }
        }

        if ($read !== null) {
            $property = $property->withReadType($read);
        }

        if ($write !== null) {
            $property = $property->withWriteType($write);
        }

        return $property;
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

    private static function findSetHookType(PropertyContext $context, \ReflectionMethod $set): ?TypeMetadata
    {
        $parameter = $set->getParameters()[0]->name ?? '';

        if ($parameter === '') {
            return null;
        }

        return $context->createType($context->findTag(
            docComment: $set->getDocComment(),
            class: ParamTag::class,
            variable: $parameter,
        ));
    }

    private static function createDefaultDocBlockParser(): DocBlockParser
    {
        return new DocBlockParser([
            MapperPlatform::createWithDefaultTagNames(),
        ]);
    }
}
