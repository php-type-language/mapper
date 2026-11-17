<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader\ClassIndex;
use TypeLang\Mapper\Mapping\Reader\Option\Options;
use TypeLang\Mapper\Mapping\Reader\Option\TypeOption\TypeDirection;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigPath;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given properties and of the class with a config:
 * a map of class names to class configs. A config of a property that is not
 * given is an error, so is an unknown key. Each option reads its own keys.
 * Types are kept as written, names in them are fully qualified. Class names
 * are case-insensitive, the leading `\` is optional.
 *
 * ```
 * App\Shape:
 *   discriminator:
 *     field: type
 *     map: { circle: App\Circle, square: App\Square }
 *     otherwise: App\UnknownShape
 * App\Status:
 *   aliases: { active: enabled, on: enabled }
 * App\User:
 *   type_error_message: 'User must be an object'
 *   normalize_as_array: false
 *   naming_strategy: snake_case
 *   properties:
 *     id: int
 *     email:
 *       name: e_mail
 *       aliases: [mail, email_address]
 *       alias_conflict: first
 *       type: non-empty-string
 *       read_type: string
 *       write_type: non-empty-string
 *       strict: true
 *       default: null
 *       skip: [null, 'this.isPrivate()']
 *       type_error_message: 'Email must be a string'
 *       undefined_error_message: 'Email is required'
 * ```
 */
final class ArrayMetadataReader implements MetadataReaderInterface
{
    private const PROPERTIES_KEY = 'properties';

    private readonly Options $options;

    /**
     * Keys of a class config: of the properties and of the options
     *
     * @var array<non-empty-string, true>
     */
    private readonly array $classKeys;

    /**
     * Keys of a property config read by the options
     *
     * @var array<non-empty-string, true>
     */
    private readonly array $propertyKeys;

    private ?ClassIndex $index = null;

    /**
     * @param TypeParserInterface<TypeNode, string> $parser parser of the
     *        types of the config
     * @param ExpressionLanguage|null $expressions parser of the expressions
     *        of `skip`, created on the first one in case of {@see null}
     */
    public function __construct(
        /**
         * @var array<array-key, mixed>
         */
        private readonly array $config,
        /**
         * File of the config for the place of errors, {@see null} in case
         * of the config has none
         *
         * @var non-empty-string|null
         */
        private readonly ?string $file = null,
        TypeParserInterface $parser = new TypeParser(),
        ?ExpressionLanguage $expressions = null,
    ) {
        $this->options = Options::createDefault($parser, $expressions);

        $classKeys = [self::PROPERTIES_KEY => true];

        foreach ($this->options->classes as $option) {
            foreach ($option->getClassKeys() as $key) {
                $classKeys[$key] = true;
            }
        }

        $propertyKeys = [];

        foreach ($this->options->properties as $option) {
            foreach ($option->getPropertyKeys() as $key) {
                $propertyKeys[$key] = true;
            }
        }

        $this->classKeys = $classKeys;
        $this->propertyKeys = $propertyKeys;
    }

    /**
     * @throws ConfigLoadingException in case of the config is not a map of
     *         classes
     * @throws InvalidMetadataException in case of the config of the class
     *         is wrong
     * @throws MissingDependencyException in case of an expression is given,
     *         but the `symfony/expression-language` package is not installed
     */
    public function read(\ReflectionClass $class, ?ClassMetadata $metadata = null): ClassMetadata
    {
        $metadata ??= new ClassMetadata($class->getName());
        $this->index ??= new ClassIndex($this->config, $this->file);
        $key = $this->index->findKey($class);

        if ($key === null) {
            return $metadata;
        }

        $errors = new ErrorCollector();

        $metadata = $this->readClass($class, $metadata, $key, $errors);

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg> $metadata
     * @param non-empty-string $key
     * @return ClassMetadata<TArg>
     * @throws MissingDependencyException in case of an expression is given,
     *         but the `symfony/expression-language` package is not installed
     */
    private function readClass(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        string $key,
        ErrorCollector $errors,
    ): ClassMetadata {
        $config = $this->config[$key];
        $path = ConfigPath::createRoot($this->file)->withKey($key);

        if (!\is_array($config)) {
            $errors->addError(InvalidOptionValueException::becauseClassConfigIsNotMap(
                $class->name,
                $path->createSource(),
            ));

            return $metadata;
        }

        if (\array_key_exists(self::PROPERTIES_KEY, $config)) {
            $metadata = $this->readProperties(
                class: $class,
                metadata: $metadata,
                properties: $config[self::PROPERTIES_KEY],
                path: $path->withKey(self::PROPERTIES_KEY),
                errors: $errors,
            );
        }

        $section = ConfigSection::createFromMap($class->name, $config, $path);

        foreach ($this->options->classes as $option) {
            $metadata = $option->readClassConfig($class, $section, $metadata, $errors);
        }

        foreach ($config as $name => $_) {
            if (!isset($this->classKeys[$name])) {
                $errors->addError(UnknownKeyException::becauseKeyIsUnknown(
                    $name,
                    $class->name,
                    $path->withKey($name)->createSource(),
                ));
            }
        }

        return $metadata;
    }

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassMetadata<TArg> $metadata
     * @return ClassMetadata<TArg>
     * @throws MissingDependencyException in case of an expression is given,
     *         but the `symfony/expression-language` package is not installed
     */
    private function readProperties(
        \ReflectionClass $class,
        ClassMetadata $metadata,
        mixed $properties,
        ConfigPath $path,
        ErrorCollector $errors,
    ): ClassMetadata {
        if (!\is_array($properties)) {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotMap(
                self::PROPERTIES_KEY,
                $class->name,
                $path->createSource(),
            ));

            return $metadata;
        }

        foreach ($properties as $name => $config) {
            $name = (string) $name;
            $target = MetadataException::formatPropertyTarget($class->name, $name);
            $propertyPath = $path->withKey($name);

            if ($name === '' || !$class->hasProperty($name)) {
                $errors->addError(UnknownPropertyException::becausePropertyDoesNotExist(
                    $target,
                    $propertyPath->createSource(),
                ));

                continue;
            }

            $property = $metadata->findProperty($name);

            if ($property === null) {
                $errors->addError(UnknownPropertyException::becausePropertyIsNotMapped(
                    $target,
                    $propertyPath->createSource(),
                ));

                continue;
            }

            $property = $this->readProperty($config, $property, $target, $propertyPath, $errors);

            $metadata = $metadata->withProperty($property);
        }

        return $metadata;
    }

    /**
     * @param non-empty-string $target property reference: `App\User::$id`
     * @param ConfigPath $path path of the property
     * @throws MissingDependencyException in case of an expression is given,
     *         but the `symfony/expression-language` package is not installed
     */
    private function readProperty(
        mixed $config,
        PropertyMetadata $property,
        string $target,
        ConfigPath $path,
        ErrorCollector $errors,
    ): PropertyMetadata {
        // id: int
        if (\is_string($config)) {
            $section = ConfigSection::createFromValue($target, TypeDirection::Both->value, $config, $path);

            return $this->readPropertySection($section, $property, $errors);
        }

        if (!\is_array($config)) {
            $errors->addError(InvalidOptionValueException::becausePropertyConfigIsNotStringOrMap(
                $target,
                $path->createSource(),
            ));

            return $property;
        }

        $section = ConfigSection::createFromMap($target, $config, $path);
        $property = $this->readPropertySection($section, $property, $errors);

        foreach ($config as $key => $_) {
            if (!isset($this->propertyKeys[$key])) {
                $errors->addError(UnknownKeyException::becauseKeyIsUnknown(
                    $key,
                    $target,
                    $path->withKey($key)->createSource(),
                ));
            }
        }

        return $property;
    }

    /**
     * @throws MissingDependencyException in case of an expression is given,
     *         but the `symfony/expression-language` package is not installed
     */
    private function readPropertySection(
        ConfigSection $section,
        PropertyMetadata $property,
        ErrorCollector $errors,
    ): PropertyMetadata {
        foreach ($this->options->properties as $option) {
            $property = $option->readPropertyConfig($section, $property, $errors);
        }

        return $property;
    }
}
