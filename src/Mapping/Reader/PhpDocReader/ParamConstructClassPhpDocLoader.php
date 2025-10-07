<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\PhpDocReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\PHPDoc\Exception\RuntimeExceptionInterface;
use TypeLang\PHPDoc\Parser as PhpDocParser;
use TypeLang\PHPDoc\Standard\ParamTag;
use TypeLang\PHPDoc\Tag\TagInterface;

final class ParamConstructClassPhpDocLoader extends ClassPhpDocLoader
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $paramTagName,
        private readonly PhpDocParser $parser,
    ) {}

    public function load(ClassInfo $info, \ReflectionClass $class): void
    {
        foreach ($this->getPromotedParamTags($class) as $name => $type) {
            $property = $info->getPropertyOrCreate($name);

            $property->read = $property->write = $type;
        }
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return iterable<non-empty-string, ParsedTypeInfo>
     * @throws \ReflectionException
     * @throws RuntimeExceptionInterface
     */
    protected function getPromotedParamTags(\ReflectionClass $class): iterable
    {
        do {
            $constructor = $class->getConstructor();

            // Skip in case of constructor is not defined
            if ($constructor === null) {
                continue;
            }

            $comment = $constructor->getDocComment();

            // Skip in case of docblock is not defined
            if ($comment === false) {
                continue;
            }

            foreach ($this->parser->parse($comment) as $tag) {
                // Skip all non-param tags.
                if (!$this->isExpectedParamTag($tag)) {
                    continue;
                }

                $name = $tag->getVariableName();

                // Skip in case of property not defined
                if (!$class->hasProperty($name)) {
                    continue;
                }

                $property = $class->getProperty($name);
                $propertyClass = $property->getDeclaringClass();

                // Skip in case of param tag related to
                // non-promoted or non-owned parameter.
                if (!$property->isPromoted() && $propertyClass->name !== $class->name) {
                    continue;
                }

                $type = $tag->getType();

                // Skip in case of type of param tag not defined
                if ($type === null) {
                    continue;
                }

                yield $name => new ParsedTypeInfo(
                    statement: $type,
                    source: $this->getSourceInfo($constructor),
                );
            }
        } while (($class = $class->getParentClass()) !== false);
    }

    private function getSourceInfo(\ReflectionMethod $constructor): ?SourceInfo
    {
        $file = $constructor->getFileName();
        $line = $constructor->getStartLine();

        if (\is_string($file) && $file !== '' && $line > 0) {
            return new SourceInfo($file, $line);
        }

        return null;
    }

    /**
     * @phpstan-assert-if-true ParamTag $tag
     */
    private function isExpectedParamTag(TagInterface $tag): bool
    {
        return $tag instanceof ParamTag
            && $tag->getName() === $this->paramTagName;
    }
}
