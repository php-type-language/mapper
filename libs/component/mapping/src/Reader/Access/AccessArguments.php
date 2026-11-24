<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Access;

use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Arguments of {@see MapGetter} and {@see MapSetter} of one member: the
 * names and the types of its fields, the options of the denormalization. An
 * argument of a direction the member does not take part in is skipped, an
 * empty string is an error.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class AccessArguments
{
    public function __construct(
        public readonly ?MapGetter $getter,
        public readonly ?MapSetter $setter,
    ) {}

    /**
     * @param TypeParserInterface<TypeNode, string> $parser
     */
    public function apply(
        MemberDraft $member,
        TypeParserInterface $parser,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        $getter = $this->getter;

        if ($getter !== null && $member->read !== null) {
            $name = self::findString('name', $getter->name, $context, $errors);
            $type = self::findString('type', $getter->type, $context, $errors);

            if ($name !== null) {
                $member = $member->withReadAccess($member->read->withName($name));
            }

            if ($type !== null) {
                $member = $member->withReadType($context->parseType($parser, $type));
            }
        }

        if ($this->setter === null || $member->write === null) {
            return $member;
        }

        return $this->applySetter($this->setter, $member, $parser, $context, $errors);
    }

    /**
     * @param TypeParserInterface<TypeNode, string> $parser
     */
    private function applySetter(
        MapSetter $setter,
        MemberDraft $member,
        TypeParserInterface $parser,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        $name = self::findString('name', $setter->name, $context, $errors);
        $type = self::findString('type', $setter->type, $context, $errors);
        $onTypeError = self::findString('onTypeError', $setter->onTypeError, $context, $errors);
        $onUndefinedError = self::findString('onUndefinedError', $setter->onUndefinedError, $context, $errors);

        if ($name !== null && $member->write !== null) {
            $member = $member->withWriteAccess($member->write->withName($name));
        }

        if ($type !== null) {
            $member = $member->withWriteType($context->parseType($parser, $type));
        }

        if ($setter->strict !== null) {
            $member = $member->withStrict($setter->strict);
        }

        if ($setter->onConflict !== null) {
            $member = $member->withAliasConflict($setter->onConflict);
        }

        if ($setter->aliases !== []) {
            $member = self::applyAliases($member, $setter->aliases, $context, $errors);
        }

        if ($onTypeError !== null) {
            $member = $member->withTypeErrorMessage($onTypeError);
        }

        if ($onUndefinedError !== null) {
            $member = $member->withUndefinedErrorMessage($onUndefinedError);
        }

        return $member;
    }

    /**
     * Returns the given string argument, an empty one is an error.
     *
     * @param non-empty-string $argument
     * @return non-empty-string|null
     */
    private static function findString(
        string $argument,
        ?string $value,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?string {
        if ($value !== '') {
            return $value;
        }

        $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
            $argument,
            $context->target,
            $context->source,
        ));

        return null;
    }

    /**
     * @param list<mixed> $aliases
     */
    private static function applyAliases(
        MemberDraft $member,
        array $aliases,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        $names = [];

        foreach ($aliases as $alias) {
            if (!\is_string($alias) || $alias === '') {
                $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
                    'aliases',
                    $context->target,
                    $context->source,
                ));

                return $member;
            }

            $names[] = $alias;
        }

        return $member->withAliases($names);
    }
}
