<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Source;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Parser\Exception\ParserExceptionInterface;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Parser\TypeResolver;
use TypeLang\Type\TypeNode;

/**
 * Place a property value is read from: a declaration in PHP code or a key
 * of a config. Creates types of the place.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class SourceContext
{
    public function __construct(
        /**
         * Property or class the value belongs to, for messages:
         * `App\User::$id`, `App\User`
         *
         * @var non-empty-string
         */
        public readonly string $target,
        /**
         * Source of the values of this place
         */
        public readonly SourceInfo $source,
        /**
         * Returns the resolver of the names of the types, called on the
         * first resolved type, the names are kept as written in case of
         * {@see null}
         *
         * @var (\Closure(): TypeResolver)|null
         */
        private readonly ?\Closure $resolver = null,
    ) {}

    /**
     * Resolves the names of the type by the place, returns a type with an
     * error in case of a name cannot be resolved, such as `parent` of a
     * class without a parent.
     */
    public function resolveType(TypeNode $type): TypeMetadata
    {
        if ($this->resolver === null) {
            return $this->createType($type);
        }

        try {
            return $this->createType(($this->resolver)()->resolve($type));
        } catch (\UnexpectedValueException $e) {
            return $this->createInvalidType($e);
        }
    }

    public function createType(TypeNode $type): TypeMetadata
    {
        return TypeMetadata::createFromType($type, $this->source);
    }

    /**
     * Parses the type by the parser and resolves its names, returns a type
     * with an error in case of the type cannot be parsed.
     *
     * @param TypeParserInterface<TypeNode, string> $parser
     */
    public function parseType(TypeParserInterface $parser, string $definition): TypeMetadata
    {
        try {
            $type = $parser->parse($definition);
        } catch (ParserExceptionInterface $e) {
            return $this->createInvalidType($e);
        }

        return $this->resolveType($type);
    }

    /**
     * Returns a type with the error of the failure, or with its class in
     * case of the message is empty.
     */
    public function createInvalidType(\Throwable $reason): TypeMetadata
    {
        $message = $reason->getMessage();

        if ($message === '') {
            $message = $reason::class;
        }

        return TypeMetadata::createFromError($message, $this->source);
    }
}
