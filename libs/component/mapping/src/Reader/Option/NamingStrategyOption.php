<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\NamingStrategy;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Mapper\Naming\CamelCaseNamingStrategy;
use TypeLang\Mapper\Naming\KebabCaseNamingStrategy;
use TypeLang\Mapper\Naming\NamingStrategyInterface;
use TypeLang\Mapper\Naming\PascalCaseNamingStrategy;
use TypeLang\Mapper\Naming\ScreamingSnakeCaseNamingStrategy;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

/**
 * Naming strategy of a class: {@see NamingStrategy} and the
 * `naming_strategy` key. A string is the name of a built-in strategy, as in
 * Serde `rename_all`, or the class of a strategy without required
 * constructor arguments.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class NamingStrategyOption implements ClassOptionInterface
{
    private const KEY = 'naming_strategy';

    /**
     * Built-in strategies by the name of the style written in that style.
     *
     * @var array<non-empty-string, class-string<NamingStrategyInterface>>
     */
    private const BUILTIN_STRATEGIES = [
        'camelCase' => CamelCaseNamingStrategy::class,
        'PascalCase' => PascalCaseNamingStrategy::class,
        'snake_case' => SnakeCaseNamingStrategy::class,
        'SCREAMING_SNAKE_CASE' => ScreamingSnakeCaseNamingStrategy::class,
        'kebab-case' => KebabCaseNamingStrategy::class,
    ];

    public function readClassAttributes(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassMetadata {
        $attributes = $class->getAttributes(NamingStrategy::class);

        if ($attributes === []) {
            return $metadata;
        }

        $context = $contexts->createFromClass($class);

        foreach (AttributeFactory::create($attributes, NamingStrategy::class, $context, $errors) as $attribute) {
            $strategy = $attribute->strategy;

            if (\is_string($strategy)) {
                $strategy = self::create($strategy, $context, $errors);
            }

            if ($strategy !== null) {
                $metadata = $metadata->withNamingStrategy($strategy);
            }
        }

        return $metadata;
    }

    public function getClassKeys(): array
    {
        return [self::KEY];
    }

    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassMetadata $metadata,
        ErrorCollector $errors,
    ): ClassMetadata {
        if (!$config->has(self::KEY)) {
            return $metadata;
        }

        $name = $config->get(self::KEY);
        $context = $config->createContext(self::KEY);

        if (!\is_string($name) || $name === '') {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
                self::KEY,
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        $strategy = self::create($name, $context, $errors);

        if ($strategy === null) {
            return $metadata;
        }

        return $metadata->withNamingStrategy($strategy);
    }

    /**
     * Returns {@see null} in case of the name is not of a built-in strategy
     * and of a strategy class without required arguments, the error is
     * added.
     */
    private static function create(
        string $name,
        SourceContext $context,
        ErrorCollector $errors,
    ): ?NamingStrategyInterface {
        $class = self::BUILTIN_STRATEGIES[$name] ?? \ltrim($name, '\\');

        if (
            !\is_a($class, NamingStrategyInterface::class, true)
            || !(new \ReflectionClass($class))->isInstantiable()
        ) {
            $errors->addError(InvalidOptionValueException::becauseNamingStrategyIsUnknown(
                name: $name,
                target: $context->target,
                source: $context->source,
            ));

            return null;
        }

        $constructor = (new \ReflectionClass($class))->getConstructor();

        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            $errors->addError(InvalidOptionValueException::becauseNamingStrategyRequiresArguments(
                class: $class,
                target: $context->target,
                source: $context->source,
            ));

            return null;
        }

        return new $class();
    }
}
