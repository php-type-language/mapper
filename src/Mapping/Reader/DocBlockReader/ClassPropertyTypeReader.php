<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\DocBlockReader;

use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\PHPDoc\DocBlock;
use TypeLang\PHPDoc\ParserInterface;
use TypeLang\PHPDoc\Standard\VarTag;
use TypeLang\PHPDoc\Tag\TagInterface;

final class ClassPropertyTypeReader
{
    /**
     * @param non-empty-string $varTagName
     */
    public function __construct(
        private readonly string $varTagName,
        private readonly ParserInterface $parser,
    ) {}

    /**
     * Return type for given property from docblock.
     */
    public function findType(\ReflectionProperty $property): ?TypeStatement
    {
        $phpdoc = $this->getDocBlockFromProperty($property);

        foreach ($phpdoc as $tag) {
            if ($this->isExpectedVarTag($tag)) {
                /** @var VarTag $tag */
                return $tag->getType();
            }
        }

        return null;
    }

    private function isExpectedVarTag(TagInterface $tag): bool
    {
        return $tag instanceof VarTag
            && $tag->getName() === $this->varTagName;
    }

    private function getDocBlockFromProperty(\ReflectionProperty $property): DocBlock
    {
        $comment = $property->getDocComment();

        if ($comment === false) {
            return new DocBlock();
        }

        return $this->parser->parse($comment);
    }
}
