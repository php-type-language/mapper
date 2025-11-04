<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\Builder\TypeAliasBuilder\Reason;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-covariant TType of TypeInterface = TypeInterface<mixed>
 * @template-extends NamedTypeBuilder<TType>
 */
class TypeAliasBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $aliases
     */
    public function __construct(
        array|string $aliases,
        /**
         * @var NamedTypeBuilder<TType>
         */
        protected readonly NamedTypeBuilder $delegate,
        /**
         * @internal will be used in the future to notify about the
         *           use of incorrect types
         */
        protected readonly Reason $reason = Reason::DEFAULT,
    ) {
        parent::__construct($aliases);
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
        return $this->delegate->build($statement, $types, $parser);
    }
}
