<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Extractor;

use TypeLang\Type\Literal\BoolLiteralNode;
use TypeLang\Type\Literal\FloatLiteralNode;
use TypeLang\Type\Literal\IntLiteralNode;
use TypeLang\Type\Literal\LiteralNode;
use TypeLang\Type\Literal\NullLiteralNode;
use TypeLang\Type\Literal\StringLiteralNode;
use TypeLang\Type\LogicalTypeNode\UnionTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Extracts the literal type of a value, written in the canonical form: a
 * decimal integer, a string in single quotes.
 *
 * ```
 * LiteralTypeExtractor::extract(42);                    // 42
 * LiteralTypeExtractor::extractAll(['card', 'cash']);   // 'card'|'cash'
 * ```
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class LiteralTypeExtractor
{
    public static function extract(int|float|string|bool|null $value): LiteralNode
    {
        return match (true) {
            $value === null => new NullLiteralNode(),
            \is_bool($value) => new BoolLiteralNode($value),
            \is_int($value) => new IntLiteralNode($value),
            \is_float($value) => new FloatLiteralNode($value),
            default => new StringLiteralNode($value),
        };
    }

    /**
     * Returns the literal for one value, the union of the literals for
     * several.
     *
     * @param non-empty-list<int|float|string|bool|null> $values
     */
    public static function extractAll(array $values): TypeNode
    {
        $literals = [];

        foreach ($values as $value) {
            $literals[] = self::extract($value);
        }

        if (\count($literals) === 1) {
            return $literals[0];
        }

        return new UnionTypeNode($literals);
    }
}
