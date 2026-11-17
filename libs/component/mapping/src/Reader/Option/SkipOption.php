<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\EmptySkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\ExpressionSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\SkipConditionInterface;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;

/**
 * Skip conditions of a property: {@see SkipWhenNull}, {@see SkipWhenEmpty},
 * {@see SkipWhen} and the `skip` key with one condition or a list of them
 * (`null`, `empty` or an expression over the object `this`). An expression
 * is parsed once, here. A wrong condition is an error, the others are set.
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

    public function hasPropertyAttributes(\ReflectionProperty $property): bool
    {
        return self::getAttributes($property) !== [];
    }

    /**
     * @throws MissingDependencyException in case of {@see SkipWhen} is given,
     *         but the `symfony/expression-language` package is not installed
     */
    public function readPropertyAttributes(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): PropertyMetadata {
        $attributes = self::getAttributes($property);

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
        PropertyMetadata $metadata,
        ErrorCollector $errors,
    ): PropertyMetadata {
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
     * @return list<\ReflectionAttribute<object>>
     */
    private static function getAttributes(\ReflectionProperty $property): array
    {
        $result = [];

        foreach (self::ATTRIBUTES as $class) {
            $result = [...$result, ...$property->getAttributes($class)];
        }

        return $result;
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
     * Returns {@see null} in case of the expression cannot be parsed, the
     * error is added.
     *
     * @param non-empty-string $variable name of the object in the expression
     * @throws MissingDependencyException in case of the
     *         `symfony/expression-language` package is not installed
     */
    private function createExpression(
        string $expression,
        string $variable,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?ExpressionSkipCondition {
        $this->language ??= self::createLanguage();

        try {
            $parsed = $this->language->parse($expression, [$variable]);
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
