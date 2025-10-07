<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\PhpDocReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;
use TypeLang\Mapper\Mapping\Metadata\ParsedTypeInfo;
use TypeLang\PHPDoc\Parser as PhpDocParser;
use TypeLang\PHPDoc\Standard\ParamTag;
use TypeLang\PHPDoc\Tag\TagInterface;

final class WriteHookTypePropertyPhpDocLoader extends HookTypePropertyPhpDocLoader
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $paramTagName,
        private readonly PhpDocParser $parser,
    ) {}

    public function load(PropertyInfo $info, \ReflectionProperty $property): void
    {
        if (!self::isHooksSupported()) {
            return;
        }

        $hook = $property->getHook(\PropertyHookType::Set);

        // Skip in case of "set" hook not defined
        if ($hook === null) {
            return;
        }

        $variable = $this->getSetterVariableName($hook);

        // Skip in case of variable of "set" hook not defined.
        //
        // This means that there is no parameter to which
        // the "@param" tag can be attached.
        if ($variable === null) {
            return;
        }

        $comment = $hook->getDocComment();

        // Skip in case of docblock not defined
        if ($comment === false) {
            return;
        }

        foreach ($this->parser->parse($comment) as $tag) {
            // Skip all non-param tags.
            if (!$this->isExpectedParamTag($tag)) {
                continue;
            }

            // Skip all param tags that do not relate
            // to the real setter parameter.
            if ($tag->getVariableName() !== $variable) {
                continue;
            }

            // Skip in case of param tag does not
            // contain type statement.
            if (($type = $tag->getType()) === null) {
                continue;
            }

            $info->write = new ParsedTypeInfo(
                statement: $type,
                source: $this->getSourceInfo($hook),
            );
        }
    }

    /**
     * @return non-empty-string|null
     */
    private function getSetterVariableName(\ReflectionMethod $method): ?string
    {
        foreach ($method->getParameters() as $parameter) {
            /** @var non-empty-string : Parameter name cannot be empty */
            return $parameter->name;
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
