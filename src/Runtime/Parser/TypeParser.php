<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use JetBrains\PhpStorm\Language;
use Phplrt\Source\Source;
use TypeLang\Mapper\Platform\GrammarFeature;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;

final class TypeParser implements TypeParserInterface
{
    public function __construct(
        private readonly ParserInterface $parser,
    ) {}

    /**
     * TODO should me moved to an external factory class
     */
    public static function createFromPlatform(PlatformInterface $platform): self
    {
        return new self(new Parser(
            conditional: $platform->isFeatureSupported(GrammarFeature::Conditional),
            shapes: $platform->isFeatureSupported(GrammarFeature::Shapes),
            callables: $platform->isFeatureSupported(GrammarFeature::Callables),
            literals: $platform->isFeatureSupported(GrammarFeature::Literals),
            generics: $platform->isFeatureSupported(GrammarFeature::Generics),
            union: $platform->isFeatureSupported(GrammarFeature::Union),
            intersection: $platform->isFeatureSupported(GrammarFeature::Intersection),
            list: $platform->isFeatureSupported(GrammarFeature::List),
            offsets: $platform->isFeatureSupported(GrammarFeature::Offsets),
            hints: $platform->isFeatureSupported(GrammarFeature::Hints),
            attributes: $platform->isFeatureSupported(GrammarFeature::Attributes),
        ));
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        // Fast-built optimization: if the definition is "null", return a null literal.
        if ($definition === 'null') {
            return new NullLiteralNode();
        }

        return $this->parser->parse(new Source($definition));
    }
}
