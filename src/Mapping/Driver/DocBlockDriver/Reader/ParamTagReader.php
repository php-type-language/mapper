<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\DocBlockDriver\Reader;

use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\PHPDoc\DocBlock;
use TypeLang\PHPDoc\ParserInterface;
use TypeLang\PHPDoc\Standard\ParamTag;
use TypeLang\PHPDoc\Tag\TagInterface;

final class ParamTagReader implements TagReaderInterface
{
    /**
     * @var array<class-string, DocBlock>
     */
    private array $constructors = [];

    /**
     * @param non-empty-string $paramTagName
     */
    public function __construct(
        private readonly string $paramTagName,
        private readonly VarTagReader $varTag,
        private readonly ParserInterface $parser,
    ) {}

    /**
     * Return type for given property from docblock.
     */
    public function findType(\ReflectionProperty $property, PropertyMetadata $meta): ?TypeStatement
    {
        $result = $this->varTag->findType($property, $meta);

        if ($result !== null) {
            return $result;
        }

        $class = $property->getDeclaringClass();

        $phpdoc = $this->constructors[$class->getName()]
            ??= $this->getDocBlockFromPromotedProperty($class);

        foreach ($phpdoc as $tag) {
            if ($this->isExpectedParamTag($tag, $meta)) {
                /** @var ParamTag $tag */
                return $tag->getType();
            }
        }

        return null;
    }

    /**
     * Cleanup memory after task completion.
     */
    public function cleanup(): void
    {
        $this->constructors = [];
    }

    private function isExpectedParamTag(TagInterface $tag, PropertyMetadata $meta): bool
    {
        return $tag instanceof ParamTag
            && $tag->getName() === $this->paramTagName
            && $tag->getVariableName() === $meta->name;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function getDocBlockFromPromotedProperty(\ReflectionClass $class): DocBlock
    {
        $constructor = $class->getConstructor();

        if ($constructor === null) {
            return new DocBlock();
        }

        $comment = $constructor->getDocComment();

        if ($comment === false) {
            return new DocBlock();
        }

        return $this->parser->parse($comment);
    }
}
