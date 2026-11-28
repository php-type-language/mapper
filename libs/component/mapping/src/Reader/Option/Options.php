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
    public function __construct(
        /**
         * @var list<PropertyOptionInterface>
         */
        public readonly array $properties,
        /**
         * @var list<ClassOptionInterface>
         */
        public readonly array $classes,
        /**
         * Conditions of the getters and the hooks
         */
        public readonly SkipOption $skip,
        /**
         * Strategy of the class, read for its parents too
         */
        public readonly NamingStrategyOption $naming,
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
        $aliases = new AliasOption();
        $skip = new SkipOption($expressions);
        $naming = new NamingStrategyOption();

        return new self(
            properties: [
                new NameOption(),
                $aliases,
                $types,
                new DefaultOption(),
                $skip,
                $messages,
                new SensitiveOption(),
            ],
            classes: [
                new DiscriminatorOption($parser),
                $messages,
                new NormalizeAsArrayOption(),
                $naming,
                $aliases,
            ],
            skip: $skip,
            naming: $naming,
        );
    }
}
