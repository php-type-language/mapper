<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\AttributeDriver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Metadata\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class SkipConditionsPropertyMetadataLoader extends PropertyMetadataLoader
{
    public function __construct(
        private ?ExpressionLanguage $expression = null,
    ) {}

    /**
     * @throws \Throwable
     */
    public function load(
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        $this->loadExpressionConditions($property, $metadata);
        $this->loadEmptyCondition($property, $metadata);
        $this->loadNullCondition($property, $metadata);
    }

    private function loadExpressionConditions(\ReflectionProperty $property, PropertyMetadata $metadata): void
    {
        $conditions = $this->getAllPropertyAttributes($property, SkipWhen::class);

        foreach ($conditions as $condition) {
            $metadata->addSkipCondition(new ExpressionConditionMetadata(
                expression: $this->createExpression($condition->expr, [
                    $condition->context,
                ]),
                variable: $condition->context,
            ));
        }
    }

    private function loadEmptyCondition(\ReflectionProperty $property, PropertyMetadata $metadata): void
    {
        $condition = $this->findPropertyAttribute($property, SkipWhenEmpty::class);

        if ($condition === null) {
            return;
        }

        $metadata->addSkipCondition(new EmptyConditionMetadata());
    }

    private function loadNullCondition(\ReflectionProperty $property, PropertyMetadata $metadata): void
    {
        $condition = $this->findPropertyAttribute($property, SkipWhenNull::class);

        if ($condition === null) {
            return;
        }

        $metadata->addSkipCondition(new NullConditionMetadata());
    }

    /**
     * @param non-empty-string $expression
     * @param list<non-empty-string> $names
     *
     * @throws ComposerPackageRequiredException
     */
    private function createExpression(string $expression, array $names): ParsedExpression
    {
        $parser = $this->getExpressionLanguage();

        return $parser->parse($expression, $names);
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private function createDefaultExpressionLanguage(): ExpressionLanguage
    {
        if (!\class_exists(ExpressionLanguage::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'symfony/expression-language',
                purpose: 'expressions support',
            );
        }

        return new ExpressionLanguage();
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private function getExpressionLanguage(): ExpressionLanguage
    {
        return $this->expression ??= $this->createDefaultExpressionLanguage();
    }
}
