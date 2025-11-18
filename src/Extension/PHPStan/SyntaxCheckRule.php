<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\PHPStan;

use PhpParser\Node as PhpNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use TypeLang\Parser\Exception\FeatureNotAllowedException;
use TypeLang\Parser\Exception\ParseException;
use TypeLang\Parser\Exception\SemanticException;
use TypeLang\Parser\Parser;
use TypeLang\Parser\ParserInterface;

/**
 * @template T of PhpNode
 * @template-implements Rule<T>
 */
abstract class SyntaxCheckRule implements Rule
{
    /**
     * @var non-empty-string
     */
    public const ERROR_SYNTAX_IDENTIFIER = 'mapper.parser.syntaxError';

    /**
     * @var non-empty-string
     */
    public const ERROR_SEMANTIC_IDENTIFIER = 'mapper.parser.semanticError';

    /**
     * @var non-empty-string
     */
    public const ERROR_DISABLED_FEATURE_IDENTIFIER = 'mapper.parser.disabledFeatureError';

    /**
     * @var non-empty-string
     */
    public const ERROR_PARSER_ERROR = 'mapper.parser.parserError';

    private readonly ParserInterface $parser;

    /**
     * @var array<non-empty-string, RuleError|null>
     */
    private array $errors = [];

    public function __construct()
    {
        $this->parser = new Parser();
    }

    protected function checkSyntax(string $expression): ?RuleError
    {
        if (isset($this->errors[$expression])) {
            return $this->errors[$expression];
        }

        return $this->errors[$expression] = $this->doCheckSyntax($expression);
    }

    private function doCheckSyntax(string $expression): ?RuleError
    {
        try {
            $this->parser->parse($expression);
        } catch (FeatureNotAllowedException $e) {
            return $this->createError(
                message: $e->getMessage(),
                identifier: self::ERROR_DISABLED_FEATURE_IDENTIFIER,
            );
        } catch (SemanticException $e) {
            return $this->createError(
                message: $e->getMessage(),
                identifier: self::ERROR_SEMANTIC_IDENTIFIER,
            );
        } catch (ParseException $e) {
            return $this->createError(
                message: $e->getMessage(),
                identifier: self::ERROR_SYNTAX_IDENTIFIER,
            );
        } catch (\Throwable $e) {
            return $this->createError(
                message: $e->getMessage(),
                identifier: self::ERROR_PARSER_ERROR,
            );
        }

        return null;
    }

    private function createError(string $message, string $identifier): RuleError
    {
        return RuleErrorBuilder::message($message)
            ->addTip('Please check the syntax for correctness')
            ->identifier($identifier)
            ->build();
    }
}
