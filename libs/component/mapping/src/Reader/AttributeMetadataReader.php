<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\MapAlias;
use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\NamingStrategy;
use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;
use TypeLang\Mapper\Mapping\Reader\Option\Options;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given properties and of the class with the
 * attributes, an attribute of a property that is not given is an error.
 * Each option reads its own attributes: {@see MapName}, {@see MapType},
 * {@see SkipWhenNull}, {@see SkipWhenEmpty}, {@see SkipWhen},
 * {@see OnTypeError}, {@see OnUndefinedError} of a property,
 * {@see DiscriminatorMap}, {@see OnTypeError}, {@see NormalizeAsArray},
 * {@see NamingStrategy} of a class, {@see MapAlias} of a case. Names in the
 * types are resolved by the file declaring the property or the class.
 */
final class AttributeMetadataReader implements MetadataReaderInterface
{
    private readonly SourceContextFactory $contexts;

    private readonly Options $options;

    /**
     * @param TypeParserInterface<TypeNode, string> $parser parser of the
     *        types of {@see MapType}
     * @param ExpressionLanguage|null $expressions parser of the expressions of
     *        {@see SkipWhen}, created on the first one in case of {@see null}
     */
    public function __construct(
        TypeParserInterface $parser = new TypeParser(),
        ?ExpressionLanguage $expressions = null,
    ) {
        $this->contexts = new SourceContextFactory();
        $this->options = Options::createDefault($parser, $expressions);
    }

    /**
     * @throws InvalidMetadataException in case of an attribute is wrong,
     *         belongs to a property that is not given or to a method
     * @throws MissingDependencyException in case of {@see SkipWhen} is
     *         given, but the `symfony/expression-language` package is not
     *         installed
     */
    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        $metadata ??= new ClassMetadata($class->getName());
        $errors = new ErrorCollector();

        foreach ($class->getProperties() as $property) {
            $metadata = $this->readProperty($class, $property, $metadata, $errors);
        }

        foreach ($this->options->classes as $option) {
            $metadata = $option->readClassAttributes($class, $metadata, $this->contexts, $errors);
        }

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg> $metadata
     * @return ClassMetadata<TArg>
     * @throws MissingDependencyException in case of {@see SkipWhen} is
     *         given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readProperty(
        \ReflectionClass $class,
        \ReflectionProperty $reflection,
        ClassMetadata $metadata,
        ErrorCollector $errors,
    ): ClassMetadata {
        $options = [];

        foreach ($this->options->properties as $option) {
            if ($option->hasPropertyAttributes($reflection)) {
                $options[] = $option;
            }
        }

        if ($reflection->name === '' || $options === []) {
            return $metadata;
        }

        $context = $this->contexts->createFromReflection($class, $reflection);
        $property = $metadata->findProperty($reflection->name);

        if ($property === null) {
            $errors->addError(UnknownPropertyException::becausePropertyIsNotMapped(
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        foreach ($options as $option) {
            $property = $option->readPropertyAttributes($reflection, $property, $context, $errors);
        }

        return $metadata->withProperty($property);
    }
}
