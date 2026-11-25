<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\Reader\Access\AccessDirection;
use TypeLang\Mapper\Mapping\Reader\Access\AccessFactory;
use TypeLang\Mapper\Mapping\Reader\Access\ConfigAccessReader;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader\ClassIndex;
use TypeLang\Mapper\Mapping\Reader\Option\Options;
use TypeLang\Mapper\Mapping\Reader\Option\TypeOption;
use TypeLang\Mapper\Mapping\Reader\Reflection\ClassProperties;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertyHooks;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigPath;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Reader\ReaderInterface;
use TypeLang\Reader\ReflectionReader;
use TypeLang\Type\TypeNode;

/**
 * Overrides values of the given members and of the class with a config: a
 * map of class names to class configs. A config of a property that is not
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
 *       strict: true
 *       default: null
 *       skip: [null, 'this.isPrivate()']
 *       type_error_message: 'Email must be a string'
 *       undefined_error_message: 'Email is required'
 *     createdAt:
 *       get: { name: created_at }
 *     title:
 *       get: { hook: true }
 *       set: { hook: true, type: string }
 *   methods:
 *     getFullName:
 *       get: { name: full_name, skip: empty }
 * ```
 *
 * The keys of a property apply to both its directions. The `get` and `set`
 * keys declare the directions instead, see {@see self::readAccess()}, the
 * other keys are given in them. A method requires the name of its field.
 */
final class ArrayMetadataReader implements AccessSourceReaderInterface
{
    private const PROPERTIES_KEY = 'properties';

    private const METHODS_KEY = 'methods';

    /**
     * Keys of the `get` section read by the options
     *
     * @var list<non-empty-string>
     */
    private const READ_KEYS = ['name', 'type', 'skip'];

    /**
     * Keys of the `set` section read by the options
     *
     * @var list<non-empty-string>
     */
    private const WRITE_KEYS = [
        'name',
        'type',
        'aliases',
        'alias_conflict',
        'strict',
        'default',
        'type_error_message',
        'undefined_error_message',
    ];

    /**
     * Keys of the denormalization among the keys of both directions,
     * `strict` is checked by its option
     *
     * @var list<non-empty-string>
     */
    private const WRITE_ONLY_KEYS = [
        'aliases',
        'alias_conflict',
        'default',
        'type_error_message',
        'undefined_error_message',
    ];

    /**
     * Keys of the normalization among the keys of both directions
     *
     * @var list<non-empty-string>
     */
    private const READ_ONLY_KEYS = ['skip'];

    private readonly Options $options;

    private readonly ConfigAccessReader $access;

    /**
     * Keys of a class config: of the members and of the options
     *
     * @var array<non-empty-string, true>
     */
    private readonly array $classKeys;

    /**
     * Keys of a member config read by the options
     *
     * @var array<non-empty-string, true>
     */
    private readonly array $memberKeys;

    private ?ClassIndex $index = null;

    /**
     * @param TypeParserInterface<TypeNode, string> $parser parser of the
     *        types of the config
     * @param ExpressionLanguage|null $expressions parser of the expressions
     *        of `skip`, created on the first one in case of {@see null}
     * @param ReaderInterface $nativeTypeReader reader of the native types of
     *        the properties, hooks and methods
     */
    public function __construct(
        /**
         * @var array<array-key, mixed>
         */
        private readonly array $config,
        /**
         * File the config is loaded from, the place of the errors, in case of
         * the config is given in code {@see null}
         *
         * @var non-empty-string|null
         */
        private readonly ?string $file = null,
        TypeParserInterface $parser = new TypeParser(),
        ?ExpressionLanguage $expressions = null,
        ReaderInterface $nativeTypeReader = new ReflectionReader(),
    ) {
        $this->options = Options::createDefault($parser, $expressions);
        $this->access = new ConfigAccessReader(new AccessFactory($nativeTypeReader));

        $classKeys = [self::PROPERTIES_KEY => true, self::METHODS_KEY => true];

        foreach ($this->options->classes as $option) {
            foreach ($option->getClassKeys() as $key) {
                $classKeys[$key] = true;
            }
        }

        $memberKeys = [];

        foreach ($this->options->properties as $option) {
            foreach ($option->getPropertyKeys() as $key) {
                $memberKeys[$key] = true;
            }
        }

        $this->classKeys = $classKeys;
        $this->memberKeys = $memberKeys;
    }

    /**
     * @throws InvalidMetadataException in case of a value is wrong, a key is
     *         unknown or belongs to a member that is not given
     * @throws ConfigLoadingException in case of the config has two keys of
     *         one class
     * @throws MissingDependencyException in case of an expression of `skip`
     *         is given, but the `symfony/expression-language` package is not
     *         installed
     */
    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        $metadata ??= new ClassDraft($class->getName());
        $key = $this->findKey($class);
        $errors = new ErrorCollector();

        if ($key !== null) {
            $metadata = $this->readClass($class, $metadata, $key, $errors);
        }

        $metadata = $this->readParentStrategies($class, $metadata, $errors);

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * Reads the naming strategies of the parents for the fields they
     * declare.
     *
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     * @throws ConfigLoadingException in case of the config has two keys of
     *         one class
     */
    private function readParentStrategies(
        \ReflectionClass $class,
        ClassDraft $metadata,
        ErrorCollector $errors,
    ): ClassDraft {
        for ($parent = $class->getParentClass(); $parent !== false; $parent = $parent->getParentClass()) {
            $key = $this->findKey($parent);
            $config = $key === null ? null : $this->config[$key];

            if (!\is_array($config)) {
                continue;
            }

            $path = ConfigPath::createRoot($this->file)->withKey((string) $key);
            $section = ConfigSection::createFromMap($parent->name, $config, $path);

            $metadata = $this->options->naming->readParentConfig($parent, $section, $metadata, $errors);
        }

        return $metadata;
    }

    /**
     * Reads the members declared by the `get` and `set` keys of the
     * properties and by the `methods` section.
     *
     * @throws InvalidMetadataException in case of a key declaring a member is
     *         wrong
     * @throws ConfigLoadingException in case of the config has two keys of
     *         one class
     */
    public function readAccess(\ReflectionClass $class, ClassDraft $metadata): ClassDraft
    {
        $key = $this->findKey($class);
        $config = $key === null ? null : $this->config[$key];

        if (!\is_array($config)) {
            return $metadata;
        }

        $errors = new ErrorCollector();
        $path = ConfigPath::createRoot($this->file)->withKey((string) $key);

        foreach (self::getMap($config, self::PROPERTIES_KEY) as $name => $values) {
            $name = (string) $name;

            $property = ClassProperties::findProperty($class, $name);

            if ($name === '' || !\is_array($values) || $property === null) {
                continue;
            }
            $target = MetadataException::formatPropertyTarget($class->name, $name);
            $section = ConfigSection::createFromMap(
                $target,
                $values,
                $path->withKey(self::PROPERTIES_KEY)->withKey($name),
            );
            $member = $this->access->readProperty($class, $property, $section, $errors);

            if ($member !== null) {
                $metadata = $metadata->withMember($member);
            }
        }

        foreach (self::getMap($config, self::METHODS_KEY) as $name => $values) {
            $name = (string) $name;

            if ($name === '' || !\is_array($values) || !$class->hasMethod($name)) {
                continue;
            }

            $method = $class->getMethod($name);
            $target = AccessFactory::formatMethod($class, $method);
            $section = ConfigSection::createFromMap(
                $target,
                $values,
                $path->withKey(self::METHODS_KEY)->withKey($name),
            );
            $member = $this->access->readMethod($class, $method, $section, $errors);

            if ($member !== null) {
                $metadata = $metadata->withMember($member);
            }
        }

        $errors->throwIfNotEmpty($metadata);

        return $metadata;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @throws ConfigLoadingException in case of the config has two keys of
     *         one class
     */
    private function findKey(\ReflectionClass $class): ?string
    {
        $this->index ??= new ClassIndex($this->config, $this->file);

        return $this->index->findKey($class);
    }

    /**
     * @param array<array-key, mixed> $config
     * @return array<array-key, mixed>
     */
    private static function getMap(array $config, string $key): array
    {
        $value = $config[$key] ?? null;

        return \is_array($value) ? $value : [];
    }

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     * @throws MissingDependencyException in case of an expression of `skip`
     *         is given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readClass(
        \ReflectionClass $class,
        ClassDraft $metadata,
        string $key,
        ErrorCollector $errors,
    ): ClassDraft {
        $config = $this->config[$key];
        $path = ConfigPath::createRoot($this->file)->withKey($key);

        if (!\is_array($config)) {
            $errors->addError(InvalidOptionValueException::becauseClassConfigIsNotMap(
                $class->name,
                $path->createSource(),
            ));

            return $metadata;
        }

        foreach ([self::PROPERTIES_KEY, self::METHODS_KEY] as $section) {
            if (\array_key_exists($section, $config) && !\is_array($config[$section])) {
                $errors->addError(InvalidOptionValueException::becauseValueIsNotMap(
                    $section,
                    $class->name,
                    $path->withKey($section)->createSource(),
                ));
            }
        }

        foreach (self::getMap($config, self::PROPERTIES_KEY) as $name => $values) {
            $metadata = $this->readProperty($class, $metadata, (string) $name, $values, $path, $errors);
        }

        foreach (self::getMap($config, self::METHODS_KEY) as $name => $values) {
            $metadata = $this->readMethod($class, $metadata, (string) $name, $values, $path, $errors);
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
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     * @throws MissingDependencyException in case of an expression of `skip`
     *         is given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readProperty(
        \ReflectionClass $class,
        ClassDraft $metadata,
        string $name,
        mixed $config,
        ConfigPath $path,
        ErrorCollector $errors,
    ): ClassDraft {
        $target = MetadataException::formatPropertyTarget($class->name, $name);
        $path = $path->withKey(self::PROPERTIES_KEY)->withKey($name);

        $property = ClassProperties::findProperty($class, $name);

        if ($name === '' || $property === null) {
            $errors->addError(UnknownPropertyException::becausePropertyDoesNotExist($target, $path->createSource()));

            return $metadata;
        }

        $section = self::toSection($target, $config, $path);

        if ($section === null) {
            $errors->addError(InvalidOptionValueException::becausePropertyConfigIsNotStringOrMap(
                $target,
                $path->createSource(),
            ));

            return $metadata;
        }

        $key = ClassProperties::getKey($class, $property);
        $member = null;

        if ($key !== '') {
            $member = $metadata->findMember($key);
        }

        if ($member === null) {
            $error = UnknownPropertyException::becausePropertyIsNotMapped($target, $path->createSource());

            if (PropertyHooks::isVirtual($property)) {
                $error = InvalidOptionValueException::becausePropertyIsVirtual($target, $path->createSource());
            }

            if ($property->isStatic()) {
                $error = UnknownPropertyException::becausePropertyIsStatic($target, $path->createSource());
            }

            $errors->addError($error);

            return $metadata;
        }

        return $metadata->withMember($this->readMember($section, $member, [ConfigAccessReader::HOOK_KEY], $errors));
    }

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @param ClassDraft<TArg> $metadata
     * @return ClassDraft<TArg>
     * @throws MissingDependencyException in case of an expression of `skip`
     *         is given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readMethod(
        \ReflectionClass $class,
        ClassDraft $metadata,
        string $name,
        mixed $config,
        ConfigPath $path,
        ErrorCollector $errors,
    ): ClassDraft {
        $path = $path->withKey(self::METHODS_KEY)->withKey($name);

        if ($name === '' || !$class->hasMethod($name)) {
            $errors->addError(InvalidOptionValueException::becauseMethodDoesNotExist(
                $name,
                $class->name,
                $path->createSource(),
            ));

            return $metadata;
        }

        $method = $class->getMethod($name);
        $target = AccessFactory::formatMethod($class, $method);

        if (!\is_array($config)) {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotMap(
                $name,
                $target,
                $path->createSource(),
            ));

            return $metadata;
        }

        $member = $metadata->findMember($method->name . '()');

        if ($member === null) {
            return $metadata;
        }

        $section = ConfigSection::createFromMap($target, $config, $path);

        return $metadata->withMember($this->readMember($section, $member, [], $errors));
    }

    /**
     * Returns the config of the property: a map or a type written in place
     * of the map (`id: int`), {@see null} in case of another value.
     *
     * @param non-empty-string $target
     */
    private static function toSection(string $target, mixed $config, ConfigPath $path): ?ConfigSection
    {
        if (\is_string($config)) {
            return ConfigSection::createFromValue($target, TypeOption::TYPE_KEY, $config, $path);
        }

        if (\is_array($config)) {
            return ConfigSection::createFromMap($target, $config, $path);
        }

        return null;
    }

    /**
     * Reads the keys of both directions or the `get` and `set` sections.
     *
     * @param list<non-empty-string> $accessKeys keys of the `get` and `set`
     *        sections read with the access
     * @throws MissingDependencyException in case of an expression of `skip`
     *         is given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readMember(
        ConfigSection $config,
        MemberDraft $member,
        array $accessKeys,
        ErrorCollector $errors,
    ): MemberDraft {
        if (!ConfigAccessReader::hasDirections($config)) {
            $member = $this->readBothDirections($config, $member, $errors);

            self::reportUnknownKeys($config, \array_keys($this->memberKeys), $errors);

            return $member;
        }

        foreach ($config->getKeys() as $key) {
            $key = (string) $key;

            if ($key === '') {
                continue;
            }

            if (isset($this->memberKeys[$key])) {
                $context = $config->createContext($key);
                $errors->addError(InvalidOptionValueException::becauseKeyIsNextToDirections(
                    $key,
                    $context->target,
                    $context->source,
                ));
            } elseif ($key !== AccessDirection::Read->getConfigKey()
                && $key !== AccessDirection::Write->getConfigKey()) {
                $context = $config->createContext($key);
                $errors->addError(UnknownKeyException::becauseKeyIsUnknown($key, $context->target, $context->source));
            }
        }

        $read = $config->findSection(AccessDirection::Read->getConfigKey());

        if ($read !== null && $member->read !== null) {
            self::reportUnknownKeys($read, [...self::READ_KEYS, ...$accessKeys], $errors);

            $one = $this->readOptions($read->withOnly(self::READ_KEYS), $member->withWriteAccess(null), $errors);
            $member = $one->withWriteAccess($member->write);
        }

        $write = $config->findSection(AccessDirection::Write->getConfigKey());

        if ($write !== null && $member->write !== null) {
            self::reportUnknownKeys($write, [...self::WRITE_KEYS, ...$accessKeys], $errors);

            $one = $this->readOptions($write->withOnly(self::WRITE_KEYS), $member->withReadAccess(null), $errors);
            $member = $one->withReadAccess($member->read);
        }

        return $member;
    }

    /**
     * Reads the keys of both directions, a key of the direction the member
     * does not take part in is an error.
     *
     * @throws MissingDependencyException in case of an expression of `skip`
     *         is given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readBothDirections(
        ConfigSection $config,
        MemberDraft $member,
        ErrorCollector $errors,
    ): MemberDraft {
        $missing = [];

        if ($member->write === null) {
            $missing[AccessDirection::Write->getParticiple()] = self::WRITE_ONLY_KEYS;
        }

        if ($member->read === null) {
            $missing[AccessDirection::Read->getParticiple()] = self::READ_ONLY_KEYS;
        }

        foreach ($missing as $direction => $keys) {
            foreach ($keys as $key) {
                if (!$config->has($key)) {
                    continue;
                }

                $context = $config->createContext($key);
                $errors->addError(InvalidOptionValueException::becauseDirectionIsMissing(
                    $key,
                    $direction,
                    $context->target,
                    $context->source,
                ));
            }
        }

        return $this->readOptions($config, $member, $errors);
    }

    /**
     * @throws MissingDependencyException in case of an expression of `skip`
     *         is given, but the `symfony/expression-language` package is not
     *         installed
     */
    private function readOptions(ConfigSection $config, MemberDraft $member, ErrorCollector $errors): MemberDraft
    {
        foreach ($this->options->properties as $option) {
            $member = $option->readPropertyConfig($config, $member, $errors);
        }

        return $member;
    }

    /**
     * @param list<string> $known
     */
    private static function reportUnknownKeys(ConfigSection $config, array $known, ErrorCollector $errors): void
    {
        foreach ($config->getKeys() as $key) {
            $key = (string) $key;

            if ($key === '' || \in_array($key, $known, true)) {
                continue;
            }

            $context = $config->createContext($key);
            $errors->addError(UnknownKeyException::becauseKeyIsUnknown($key, $context->target, $context->source));
        }
    }
}
