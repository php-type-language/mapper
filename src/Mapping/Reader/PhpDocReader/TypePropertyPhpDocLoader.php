<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\PhpDocReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\PHPDoc\Parser as PhpDocParser;
use TypeLang\PHPDoc\Standard\VarTag;
use TypeLang\PHPDoc\Tag\TagInterface;

final class TypePropertyPhpDocLoader extends PropertyPhpDocLoader
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $varTagName,
        private readonly PhpDocParser $parser,
    ) {}

    public function load(PropertyInfo $info, \ReflectionProperty $property): void
    {
        $comment = $property->getDocComment();

        if ($comment === false) {
            return;
        }

        foreach ($this->parser->parse($comment) as $tag) {
            if (!$this->isExpectedVarTag($tag)) {
                continue;
            }

            $info->read = $info->write = new ParsedTypeInfo(
                statement: $tag->getType(),
                source: $this->getSourceInfo($property),
            );
        }
    }

    private function getSourceInfo(\ReflectionProperty $property): ?SourceInfo
    {
        if (!$property->isPromoted()) {
            return null;
        }

        $class = $property->getDeclaringClass();
        $constructor = $class->getConstructor();

        if ($constructor === null) {
            return null;
        }

        $file = $constructor->getFileName();
        $line = $constructor->getStartLine();

        if (\is_string($file) && $file !== '' && $line > 0) {
            return new SourceInfo($file, $line);
        }

        return null;
    }

    /**
     * @phpstan-assert-if-true VarTag $tag
     */
    private function isExpectedVarTag(TagInterface $tag): bool
    {
        return $tag instanceof VarTag
            && $tag->getName() === $this->varTagName;
    }
}
