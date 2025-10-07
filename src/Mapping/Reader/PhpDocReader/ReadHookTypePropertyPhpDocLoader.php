<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\PhpDocReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;
use TypeLang\PHPDoc\Parser as PhpDocParser;
use TypeLang\PHPDoc\Standard\ReturnTag;
use TypeLang\PHPDoc\Tag\TagInterface;

final class ReadHookTypePropertyPhpDocLoader extends HookTypePropertyPhpDocLoader
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $returnTagName,
        private readonly PhpDocParser $parser,
    ) {}

    public function load(PropertyInfo $info, \ReflectionProperty $property): void
    {
        if (!self::isHooksSupported()) {
            return;
        }

        $hook = $property->getHook(\PropertyHookType::Get);

        if ($hook === null) {
            return;
        }

        $comment = $hook->getDocComment();

        if ($comment === false) {
            return;
        }

        foreach ($this->parser->parse($comment) as $tag) {
            if (!$this->isExpectedReturnTag($tag)) {
                continue;
            }

            $info->read = new ParsedTypeInfo(
                statement: $tag->getType(),
                source: $this->getSourceInfo($hook),
            );
        }
    }

    /**
     * @phpstan-assert-if-true ReturnTag $tag
     */
    private function isExpectedReturnTag(TagInterface $tag): bool
    {
        return $tag instanceof ReturnTag
            && $tag->getName() === $this->returnTagName;
    }
}
