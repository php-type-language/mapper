<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\PHPStan;

use PhpParser\Node\Arg;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use TypeLang\Parser\Exception\ParserExceptionInterface;
use TypeLang\Parser\TypeParser;

/**
 * Checks the syntax of the types written as constant strings.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Bridge\PHPStan
 */
final class TypeSyntaxChecker
{
    /**
     * @var non-empty-string
     */
    public const ERROR_IDENTIFIER = 'typeLangMapper.typeSyntax';

    /**
     * Errors of the checked strings, {@see null} for a correct one
     *
     * @var array<string, IdentifierRuleError|null>
     */
    private array $errors = [];

    public function __construct(
        private readonly TypeParser $parser = new TypeParser(),
    ) {}

    /**
     * Returns the argument holding the type: the named one or the one at
     * the position, {@see null} in case of the call has none.
     *
     * @param array<array-key, mixed> $arguments
     * @param non-empty-string $name
     * @param int<0, max> $position
     */
    public function findArgument(array $arguments, string $name, int $position): ?Arg
    {
        foreach ($arguments as $index => $argument) {
            if (!$argument instanceof Arg || $argument->unpack) {
                continue;
            }

            if ($argument->name?->toString() === $name) {
                return $argument;
            }

            if ($argument->name === null && $index === $position) {
                return $argument;
            }
        }

        return null;
    }

    /**
     * Returns an error for each constant string of the argument that is
     * not a type.
     *
     * @return list<IdentifierRuleError>
     */
    public function check(Arg $argument, Scope $scope): array
    {
        $errors = [];

        foreach ($scope->getType($argument->value)->getConstantStrings() as $string) {
            $error = $this->findError($string->getValue());

            if ($error !== null) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    private function findError(string $type): ?IdentifierRuleError
    {
        if (\array_key_exists($type, $this->errors)) {
            return $this->errors[$type];
        }

        return $this->errors[$type] = $this->createError($type);
    }

    private function createError(string $type): ?IdentifierRuleError
    {
        if ($type === '') {
            return self::buildError($type, 'the type is empty');
        }

        try {
            $this->parser->forType()->parse($type);
        } catch (ParserExceptionInterface $e) {
            return self::buildError($type, $e->getMessage());
        }

        return null;
    }

    private static function buildError(string $type, string $reason): IdentifierRuleError
    {
        return RuleErrorBuilder::message(\sprintf('Type "%s" is written wrong: %s', \addcslashes($type, '"'), $reason))
            ->identifier(self::ERROR_IDENTIFIER)
            ->build();
    }
}
