<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Options of the properties and of the classes the attribute and the config
 * readers read, in the order they are read.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class Options
{
    /**
     * @param list<PropertyOptionInterface> $properties
     * @param list<ClassOptionInterface> $classes
     */
    public function __construct(
        public readonly array $properties,
        public readonly array $classes,
    ) {}

    /**
     * @param TypeParserInterface<TypeNode, string> $parser parser of the types
     * @param ExpressionLanguage|null $expressions parser of the expressions of
     *        the skip conditions, created on the first one in case of
     *        {@see null}
     */
    public static function createDefault(TypeParserInterface $parser, ?ExpressionLanguage $expressions = null): self
    {
        $types = new TypeOption($parser);
        $messages = new MessageOption();

        return new self(
            properties: [
                new NameOption(),
                $types,
                new DefaultOption(),
                new SkipOption($expressions),
                $messages,
            ],
            classes: [
                $types,
                new DiscriminatorOption($parser),
                $messages,
                new NormalizeAsArrayOption(),
                new NamingStrategyOption(),
                new AliasOption(),
            ],
        );
    }
}
