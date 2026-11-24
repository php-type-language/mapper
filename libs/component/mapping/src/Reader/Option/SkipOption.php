<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\EmptySkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\ExpressionSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\SkipConditionInterface;
use TypeLang\Mapper\Mapping\Reader\Access\AccessDirection;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;

/**
 * Skip conditions of the field of the normalization: {@see SkipWhenNull},
 * {@see SkipWhenEmpty}, {@see SkipWhen} of a property, a getter or a `get`
 * hook and the `skip` key with one condition or a list of them
 * (`null` as a string or as a value, `empty` or an expression over the
 * object `this` and the value `value`). An expression is parsed once, here,
 * by the given language: only its functions are allowed in the expression.
 * A wrong condition is an error, the others are set.
 *
 * ```
 * skip: [null, 'this.isPrivate()']
 * ```
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class SkipOption implements PropertyOptionInterface
{
    private const KEY = 'skip';

    /**
     * @var list<class-string>
     */
    private const ATTRIBUTES = [SkipWhenNull::class, SkipWhenEmpty::class, SkipWhen::class];

    public function __construct(
        /**
         * Parser of the expressions, created on the first expression in
         * case of {@see null}
         */
        private ?ExpressionLanguage $language = null,
    ) {}

    public function getPropertyAttributes(): array
    {
        return \array_fill_keys(self::ATTRIBUTES, AccessDirection::Read);
    }

    /**
     * Returns the attributes of the conditions given on the property, the
     * method or the hook.
     *
     * @return list<\ReflectionAttribute<object>>
     */
    public static function getAttributes(\ReflectionProperty|\ReflectionMethod $reflector): array
    {
        $result = [];

        foreach (self::ATTRIBUTES as $class) {
            $result = [...$result, ...$reflector->getAttributes($class)];
        }

        return $result;
    }

    /**
     * @throws MissingDependencyException in case of {@see SkipWhen} is given,
     *         but the `symfony/expression-language` package is not installed
     */
    public function readPropertyAttributes(
        \ReflectionProperty $property,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        return $this->readAttributes($property, $metadata, $context, $errors);
    }

    /**
     * Reads the conditions given on the property, the method or the hook.
     *
     * @throws MissingDependencyException in case of {@see SkipWhen} is given,
     *         but the `symfony/expression-language` package is not installed
     */
    public function readAttributes(
        \ReflectionProperty|\ReflectionMethod $reflector,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        $attributes = self::getAttributes($reflector);

        if ($attributes === []) {
            return $metadata;
        }

        $conditions = [];

        foreach (AttributeFactory::createAll($attributes, $context, $errors) as $attribute) {
            $condition = $this->createFromAttribute($attribute, $context, $errors);

            if ($condition !== null) {
                $conditions[] = $condition;
            }
        }

        return $metadata->withSkipConditions($conditions);
    }

    public function getPropertyKeys(): array
    {
        return [self::KEY];
    }

    /**
     * @throws MissingDependencyException in case of an expression is given,
     *         but the `symfony/expression-language` package is not installed
     */
    public function readPropertyConfig(
        ConfigSection $config,
        MemberDraft $metadata,
        ErrorCollector $errors,
    ): MemberDraft {
        if (!$config->has(self::KEY)) {
            return $metadata;
        }

        $context = $config->createContext(self::KEY);
        $values = $config->get(self::KEY);

        if (!\is_array($values)) {
            $values = [$values];
        }

        $conditions = [];

        foreach ($values as $value) {
            // skip: null
            $value ??= 'null';

            if (!\is_string($value) || $value === '') {
                $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
                    self::KEY,
                    $context->target,
                    $context->source,
                ));

                continue;
            }

            $condition = match ($value) {
                'null' => new NullSkipCondition(),
                'empty' => new EmptySkipCondition(),
                default => $this->createExpression($value, 'this', $context, $errors),
            };

            if ($condition !== null) {
                $conditions[] = $condition;
            }
        }

        return $metadata->withSkipConditions($conditions);
    }

    /**
     * @throws MissingDependencyException in case of an expression is given,
     *         but the `symfony/expression-language` package is not installed
     */
    private function createFromAttribute(
        object $attribute,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?SkipConditionInterface {
        return match (true) {
            $attribute instanceof SkipWhenNull => new NullSkipCondition(),
            $attribute instanceof SkipWhenEmpty => new EmptySkipCondition(),
            $attribute instanceof SkipWhen => $this->createExpression(
                $attribute->expr,
                $attribute->context,
                $context,
                $errors,
            ),
            default => null,
        };
    }

    /**
     * Returns {@see null} in case of the expression cannot be parsed or the
     * name of the object is not a variable name other than `value`, the
     * error is added.
     *
     * @param string $variable name of the object in the expression
     * @throws MissingDependencyException in case of the
     *         `symfony/expression-language` package is not installed
     */
    private function createExpression(
        string $expression,
        string $variable,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?ExpressionSkipCondition {
        if ($variable === ExpressionSkipCondition::VALUE
            || \preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $variable) !== 1) {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotVariableName(
                'context',
                $context->target,
                $context->source,
            ));

            return null;
        }

        $this->language ??= self::createLanguage();

        try {
            $parsed = $this->language->parse($expression, [$variable, ExpressionSkipCondition::VALUE]);
        } catch (SyntaxError $e) {
            $errors->addError(InvalidOptionValueException::becauseExpressionIsInvalid(
                expression: $expression,
                target: $context->target,
                source: $context->source,
                previous: $e,
            ));

            return null;
        }

        return new ExpressionSkipCondition($parsed, $variable);
    }

    /**
     * @throws MissingDependencyException in case of the
     *         `symfony/expression-language` package is not installed
     */
    private static function createLanguage(): ExpressionLanguage
    {
        if (!\class_exists(ExpressionLanguage::class)) {
            throw MissingDependencyException::becausePackageIsNotInstalled(
                package: 'symfony/expression-language',
                purpose: 'Skipping a property by an expression',
            );
        }

        return new ExpressionLanguage();
    }
}
