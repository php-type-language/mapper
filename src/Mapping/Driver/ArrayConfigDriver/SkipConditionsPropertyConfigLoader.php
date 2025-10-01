<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver\ArrayConfigDriver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;
use TypeLang\Mapper\Mapping\Metadata\EmptyConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\ExpressionConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\NullConditionMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class SkipConditionsPropertyConfigLoader extends PropertyConfigLoader
{
    public function __construct(
        private ?ExpressionLanguage $expression = null,
    ) {}

    public function load(
        array $config,
        \ReflectionProperty $property,
        PropertyMetadata $metadata,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): void {
        if (!\array_key_exists('skip', $config)) {
            return;
        }

        if (\is_string($config['skip'])) {
            $config['skip'] = [$config['skip']];
        }

        // @phpstan-ignore-next-line : Additional DbC invariant
        assert(\is_array($config['skip']));

        foreach ($config['skip'] as $skipConfig) {
            // @phpstan-ignore-next-line : Additional DbC invariant
            assert(\is_string($skipConfig));

            $metadata->addSkipCondition(match ($skipConfig) {
                'null' => new NullConditionMetadata(),
                'empty' => new EmptyConditionMetadata(),
                default => new ExpressionConditionMetadata(
                    expression: $this->createExpression($skipConfig, [
                        ExpressionConditionMetadata::DEFAULT_CONTEXT_VARIABLE_NAME,
                    ]),
                    variable: ExpressionConditionMetadata::DEFAULT_CONTEXT_VARIABLE_NAME,
                )
            });
        }
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
