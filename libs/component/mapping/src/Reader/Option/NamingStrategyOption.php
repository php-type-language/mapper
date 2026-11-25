<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
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
 * constructor arguments. The strategy names the fields the class declares:
 * the strategies of the parents are read for the fields they declare.
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
        ClassDraft $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassDraft {
        for ($current = $class; $current !== false; $current = $current->getParentClass()) {
            $attributes = $current->getAttributes(NamingStrategy::class);

            if ($attributes === []) {
                continue;
            }

            $context = $contexts->createFromClass($current);

            foreach (AttributeFactory::create($attributes, NamingStrategy::class, $context, $errors) as $attribute) {
                $strategy = $attribute->strategy;

                if (\is_string($strategy)) {
                    $strategy = self::create($strategy, $context, $errors);
                }

                if ($strategy !== null) {
                    $metadata = self::withStrategy($current, $metadata, $strategy);
                }
            }
        }

        return $metadata;
    }

    /**
     * Returns the metadata with the strategy of the class or of a parent of
     * it.
     *
     * @template TArg of object
     * @param \ReflectionClass<object> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    private static function withStrategy(
        \ReflectionClass $class,
        ClassDraft $metadata,
        NamingStrategyInterface $strategy,
    ): ClassDraft {
        if ($class->name === $metadata->name) {
            return $metadata->withNamingStrategy($strategy);
        }

        return $metadata->withParentNamingStrategy($class->name, $strategy);
    }

    public function getClassKeys(): array
    {
        return [self::KEY];
    }

    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassDraft $metadata,
        ErrorCollector $errors,
    ): ClassDraft {
        $strategy = self::readConfigStrategy($config, $errors);

        if ($strategy === null) {
            return $metadata;
        }

        return $metadata->withNamingStrategy($strategy);
    }

    /**
     * Reads the strategy of the config of a parent of the class of the
     * metadata.
     *
     * @template TArg of object
     * @param \ReflectionClass<object> $parent
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     */
    public function readParentConfig(
        \ReflectionClass $parent,
        ConfigSection $config,
        ClassDraft $metadata,
        ErrorCollector $errors,
    ): ClassDraft {
        $strategy = self::readConfigStrategy($config, $errors);

        if ($strategy === null) {
            return $metadata;
        }

        return $metadata->withParentNamingStrategy($parent->name, $strategy);
    }

    /**
     * Returns {@see null} in case of the config has no strategy or it is
     * wrong, the error is added.
     */
    private static function readConfigStrategy(ConfigSection $config, ErrorCollector $errors): ?NamingStrategyInterface
    {
        if (!$config->has(self::KEY)) {
            return null;
        }

        $name = $config->get(self::KEY);
        $context = $config->createContext(self::KEY);

        if (!\is_string($name) || $name === '') {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
                self::KEY,
                $context->target,
                $context->source,
            ));

            return null;
        }

        return self::create($name, $context, $errors);
    }

    /**
     * Returns {@see null} in case of the name is not of a built-in strategy
     * and of a strategy class without required arguments or the constructor
     * of the strategy throws, the error is added.
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

        try {
            return new $class();
        } catch (\Throwable $e) {
            $errors->addError(InvalidOptionValueException::becauseNamingStrategyCannotBeCreated(
                class: $class,
                target: $context->target,
                source: $context->source,
                previous: $e,
            ));

            return null;
        }
    }
}
