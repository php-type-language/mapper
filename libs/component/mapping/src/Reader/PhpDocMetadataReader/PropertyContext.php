<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;

use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\PhpDoc\DocBlock\Tag\TagInterface;
use TypeLang\PhpDoc\DocBlock\Tag\TypedTag;
use TypeLang\PhpDoc\DocBlock\Tag\VariableTagInterface;
use TypeLang\PhpDoc\DocBlock\Tag\VarTag\VarTag;
use TypeLang\PhpDoc\DocBlockParser;

/**
 * Property whose docblock types are read: its declaration, the parser of
 * the docblocks and the resolution of the found types.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class PropertyContext
{
    public function __construct(
        public readonly \ReflectionProperty $reflection,
        /**
         * Class or trait whose body declares the property
         *
         * @var \ReflectionClass<object>
         */
        public readonly \ReflectionClass $scope,
        private readonly DocBlockParser $parser,
        private readonly SourceContext $sourceContext,
    ) {}

    /**
     * @param \ReflectionClass<object> $class
     */
    public static function create(
        \ReflectionClass $class,
        \ReflectionProperty $reflection,
        DocBlockParser $parser,
        SourceContextFactory $contexts,
    ): self {
        return new self(
            reflection: $reflection,
            scope: $contexts->findScope($reflection),
            parser: $parser,
            sourceContext: $contexts->createFromReflection($class, $reflection),
        );
    }

    /**
     * Returns the first tag of the class in the docblock, a tag naming a
     * variable other than the given one is skipped.
     *
     * @template TTag of TypedTag
     * @param class-string<TTag> $class
     * @return TTag|null
     */
    public function findTag(string|false $docComment, string $class, string $variable): ?TypedTag
    {
        if ($docComment === false) {
            return null;
        }

        foreach ($this->parser->parse($docComment) as $tag) {
            if ($tag instanceof $class && (self::findVariable($tag) ?? $variable) === $variable) {
                return $tag;
            }
        }

        return null;
    }

    /**
     * Returns the type of the tag resolved by the imports of the
     * declaration, {@see null} in case of there is no tag.
     */
    public function createType(?TypedTag $tag): ?TypeMetadata
    {
        if ($tag === null) {
            return null;
        }

        return $this->sourceContext->resolveType(clone $tag->type);
    }

    private static function findVariable(TagInterface $tag): ?string
    {
        if ($tag instanceof VarTag || $tag instanceof VariableTagInterface) {
            return $tag->variable;
        }

        return null;
    }
}
