<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use Symfony\Component\ExpressionLanguage\SyntaxError;
use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\FieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\ExpressionSkipCondition;
use TypeLang\Mapper\Type\ClassType;
use TypeLang\Mapper\Type\ClassType\Field;
use TypeLang\Mapper\Type\ClassType\TagCheck;
use TypeLang\Mapper\Type\Extractor\DiscriminatorTagExtractor;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of a user class by its metadata. Internal classes, such
 * as {@see \DateTimeImmutable} or {@see \stdClass}, are left to their own
 * types.
 *
 * The metadata decide the fields of each direction. The built type is
 * checked against the strategies of the mapper: a class whose objects
 * cannot be created, such as an abstract one or an enum, or a field whose
 * access cannot be executed is an error of the type.
 *
 * @template-implements TypeBuilderInterface<object>
 */
final class ClassTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return ClassType<object>|null
     * @throws TypeDefinitionException in case of a field has no type or
     *         the class cannot be created or accessed
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?ClassType
    {
        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = $node->name->toClassString();

        if ($name === null || !\class_exists($name)) {
            return null;
        }

        $class = new \ReflectionClass($name);

        if ($class->isInternal()) {
            return null;
        }

        $metadata = $context->metadata->read($class);
        $bases = self::getBases($class, $context);
        $mapped = self::findMappedParent($class, [...$bases, $metadata]);

        // a class outside the map is normalized by the fields of its parent
        if ($mapped !== null) {
            $metadata = self::withOutputOf($metadata, $context->metadata->read(new \ReflectionClass($mapped)));
        }

        $writable = $readable = [];

        foreach ($metadata->input->fields as $index => $field) {
            $writable[] = self::createProperty($class, $field, $index, $context);
        }

        foreach ($metadata->output->fields as $index => $field) {
            self::checkExpressions($class, $field, $context);

            $readable[] = self::createProperty($class, $field, $index, $context);
        }

        $context->accessors->get($metadata);

        $fields = $tags = $owners = $checks = [];

        // tags of the class in the maps of its bases and of its own one,
        // the root ones first
        foreach ([...$bases, $metadata] as $base) {
            $discriminator = $base->discriminator;

            if ($discriminator === null) {
                continue;
            }

            $field = $discriminator->field;
            $fields[] = $field;
            $checks[$field] ??= self::createTagCheck($class, $discriminator);

            $tag = DiscriminatorTagExtractor::extract(
                DiscriminatorTagExtractor::extractIndex($discriminator),
                $class->name,
            );

            if ($tag === null) {
                continue;
            }

            if (\array_key_exists($field, $tags) && $tags[$field] !== $tag) {
                throw TypeDefinitionException::becauseDiscriminatorFieldIsAmbiguous(
                    $class->name,
                    $field,
                    $owners[$field],
                    $tags[$field],
                    $base->name,
                    $tag,
                );
            }

            $tags[$field] = $tag;
            $owners[$field] ??= $base->name;
        }

        return new ClassType(
            metadata: $metadata,
            writable: $writable,
            readable: $readable,
            tagFields: $fields,
            tags: $tags,
            tagChecks: \array_values(\array_filter($checks)),
        );
    }

    /**
     * Returns the nearest parent of the class in the maps of the bases, the
     * class is normalized as it: {@see null} in case of the class itself is
     * in a map or none of its parents is.
     *
     * @param \ReflectionClass<object> $class
     * @param list<ClassMetadata<object>> $bases
     * @return class-string|null
     */
    private static function findMappedParent(\ReflectionClass $class, array $bases): ?string
    {
        $nearest = null;

        foreach ($bases as $base) {
            if ($base->discriminator === null) {
                continue;
            }

            $index = DiscriminatorTagExtractor::extractIndex($base->discriminator);

            if (isset($index[$class->name])) {
                return null;
            }

            foreach ($index as $mapped => $tag) {
                if ($class->isSubclassOf($mapped) && ($nearest === null || \is_subclass_of($mapped, $nearest))) {
                    $nearest = $mapped;
                }
            }
        }

        return $nearest;
    }

    /**
     * Returns the metadata of the class normalized by the fields of the
     * given parent.
     *
     * @template TArg of object
     * @param ClassMetadata<TArg> $metadata
     * @param ClassMetadata<object> $parent
     * @return ClassMetadata<TArg>
     */
    private static function withOutputOf(ClassMetadata $metadata, ClassMetadata $parent): ClassMetadata
    {
        return new ClassMetadata(
            name: $metadata->name,
            input: $metadata->input,
            output: $parent->output,
            discriminator: $metadata->discriminator,
        );
    }

    /**
     * Returns the tags of the class the data may have in the field of the
     * discriminator, {@see null} in case of the field is not checked: the
     * map has no tag of the class and it does not take the data with an
     * unknown tag. A field of the class with the name of the field of the
     * tag takes the checked tag.
     *
     * @param \ReflectionClass<object> $class
     */
    private static function createTagCheck(\ReflectionClass $class, DiscriminatorMetadata $discriminator): ?TagCheck
    {
        $field = $discriminator->field;
        $index = DiscriminatorTagExtractor::extractIndex($discriminator);
        $nearest = DiscriminatorTagExtractor::extract($index, $class->name);
        $isDefault = $discriminator->default !== null
            && DiscriminatorTagExtractor::extractClass($discriminator->default) === $class->name;

        if ($nearest === null && !$isDefault) {
            return null;
        }

        $owner = \array_search($nearest, $index, true);
        $tags = $known = [];

        foreach ($discriminator->map as $tag => $type) {
            if ($owner !== false && DiscriminatorTagExtractor::extractClass($type) === $owner) {
                $tags[$tag] = true;
            } else {
                $known[$tag] = true;
            }
        }

        if ($isDefault) {
            return new TagCheck($field, $tags, $known);
        }

        return new TagCheck($field, $tags);
    }

    /**
     * Returns the metadata of the interfaces and the parents of the class
     * with a discriminator, the root ones first.
     *
     * @param \ReflectionClass<object> $class
     * @return list<ClassMetadata<object>>
     */
    private static function getBases(\ReflectionClass $class, BuildContext $context): array
    {
        $bases = [];

        foreach ([...\class_implements($class->name), ...\array_reverse(\class_parents($class->name))] as $name) {
            $reflection = new \ReflectionClass($name);

            if ($reflection->isInternal()) {
                continue;
            }

            $metadata = $context->metadata->read($reflection);

            if ($metadata->discriminator !== null) {
                $bases[] = $metadata;
            }
        }

        return $bases;
    }

    /**
     * @template TArgField of FieldMetadata
     * @param \ReflectionClass<object> $class
     * @param TArgField $field
     * @param int<0, max> $index
     * @return Field<TArgField, mixed>
     * @throws TypeDefinitionException in case of the field has no type or its
     *         source cannot read it
     */
    private static function createProperty(
        \ReflectionClass $class,
        FieldMetadata $field,
        int $index,
        BuildContext $context,
    ): Field {
        $type = $field->type;

        if ($type === null) {
            throw TypeDefinitionException::becauseFieldTypeIsNotDefined($class->name, $field->name);
        }

        if ($type->type === null) {
            \assert($type->error !== null);

            throw TypeDefinitionException::becauseFieldTypeCannotBeRead($class->name, $field->name, $type->error);
        }

        return new Field($field, $index, $context->types->getType($type->type), $type->type);
    }

    /**
     * Checks the skip expressions of the field against the language of the
     * mapper, such as an expression read by another language or taken from
     * a cache with a function the language does not have.
     *
     * @param \ReflectionClass<object> $class
     * @throws TypeDefinitionException in case of an expression cannot be
     *         parsed by the language
     */
    private static function checkExpressions(
        \ReflectionClass $class,
        OutputFieldMetadata $field,
        BuildContext $context,
    ): void {
        $expressions = $context->expressions;

        if ($expressions === null) {
            return;
        }

        foreach ($field->skip as $condition) {
            if (!$condition instanceof ExpressionSkipCondition) {
                continue;
            }

            try {
                $expressions->parse(
                    (string) $condition->expression,
                    [$condition->variable, ExpressionSkipCondition::VALUE],
                );
            } catch (SyntaxError $e) {
                throw TypeDefinitionException::becauseSkipExpressionIsInvalid(
                    $class->name,
                    $field->name,
                    (string) $condition->expression,
                    $e,
                );
            }
        }
    }
}
