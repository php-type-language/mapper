<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Mapping\Exception\ConfigLoadingException;
use TypeLang\Mapper\Mapping\Exception\InvalidDefaultValueException;
use TypeLang\Mapper\Mapping\Exception\InvalidMetadataException;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\Exception\UnknownPropertyException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\EmptySkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\ExpressionSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\Reader\ArrayMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\JsonFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;
use TypeLang\Mapper\Mapping\Reader\NeonFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\OptionalMetadataReaderInterface;
use TypeLang\Mapper\Mapping\Reader\PhpFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Reader\YamlFileMetadataReader;
use TypeLang\Mapper\Mapping\Tests\Stub\NamingCaseStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PrefixNamingStrategyStub;
use TypeLang\Mapper\Mapping\Tests\Stub\PromotedStub;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Mapper\Naming\KebabCaseNamingStrategy;
use TypeLang\Mapper\Naming\NamingStrategyInterface;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;
use TypeLang\Parser\TypeParser;
use TypeLang\Parser\TypeParserFeatures;

final class ArrayMetadataReaderTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../Fixture/';

    /**
     * @return iterable<non-empty-string, array{
     *     MetadataReaderInterface,
     *     string|null,
     * }>
     */
    public static function configDataProvider(): iterable
    {
        $array = new ArrayMetadataReader(self::requireArray(self::FIXTURES . 'config.php'));

        yield 'php array' => [$array, null];
        yield 'php file' => [new PhpFileMetadataReader(self::FIXTURES . 'config.php'), 'config.php'];
        yield 'json file' => [new JsonFileMetadataReader(self::FIXTURES . 'config.json'), 'config.json'];
        yield 'yaml file' => [new YamlFileMetadataReader(self::FIXTURES . 'config.yaml'), 'config.yaml'];
        yield 'neon file' => [new NeonFileMetadataReader(self::FIXTURES . 'config.neon'), 'config.neon'];
    }

    /**
     * @return iterable<non-empty-string, array{
     *     class-string<OptionalMetadataReaderInterface>,
     * }>
     */
    public static function optionalReaderDataProvider(): iterable
    {
        yield 'json file' => [JsonFileMetadataReader::class];
        yield 'yaml file' => [YamlFileMetadataReader::class];
        yield 'neon file' => [NeonFileMetadataReader::class];
    }

    /**
     * @param class-string<OptionalMetadataReaderInterface> $reader
     */
    #[DataProvider('optionalReaderDataProvider')]
    public function testOptionalReaderIsAvailableWithItsDependency(string $reader): void
    {
        self::assertTrue($reader::isAvailable());
    }

    #[DataProvider('configDataProvider')]
    public function testConfigIsReadTheSameInEveryFormat(MetadataReaderInterface $reader, ?string $file): void
    {
        $metadata = self::readOverReflection($reader, UserStub::class);

        $id = self::getProperty($metadata, 'id');
        self::assertType('positive-int', $id->read);
        self::assertType('positive-int', $id->write);
        self::assertSame($file, self::printFile($id->read->source->file));
        self::assertSame('$[\'' . UserStub::class . '\'].properties.id', $id->read->source->path);
        self::assertNull($id->read->isStrict);
        self::assertNull($id->default);

        $email = self::getProperty($metadata, 'email');
        self::assertSame('e_mail', $email->externalName);
        self::assertType('string', $email->read);
        self::assertType('non-empty-string', $email->write);
        self::assertStringEndsWith('.properties.email.write_type', (string) $email->write->source->path);
        self::assertTrue($email->read->isStrict);
        self::assertTrue($email->write->isStrict);
        self::assertNotNull($email->default);
        self::assertSame('unknown', $email->default->value);
        self::assertStringEndsWith('.properties.email.default', (string) $email->default->source->path);

        $friend = self::getProperty($metadata, 'friend');
        self::assertType('self|null', $friend->read);
        self::assertNotNull($friend->default);
        self::assertNull($friend->default->value);
    }

    public function testHigherFileOverridesValuesOfLowerFile(): void
    {
        $reader = new ChainMetadataReader([
            new JsonFileMetadataReader(self::FIXTURES . 'override.json'),
            new JsonFileMetadataReader(self::FIXTURES . 'config.json'),
        ]);

        $metadata = self::readOverReflection($reader, UserStub::class);

        $id = self::getProperty($metadata, 'id');
        self::assertType('int', $id->read);
        self::assertSame('override.json', self::printFile($id->read->source->file));

        $email = self::getProperty($metadata, 'email');
        self::assertSame('mail', $email->externalName);
        self::assertType('non-empty-string', $email->write);
        self::assertSame('config.json', self::printFile($email->write->source->file));
    }

    public function testFileIsDecodedOnceOnFirstRead(): void
    {
        $file = self::FIXTURES . 'config.json';
        $copy = \tempnam(\sys_get_temp_dir(), 'tlm');
        self::assertIsString($copy);
        self::assertNotSame('', $copy);
        \copy($file, $copy);

        $reader = new JsonFileMetadataReader($copy);

        try {
            $first = self::readOverReflection($reader, UserStub::class);
        } finally {
            \unlink($copy);
        }

        $second = self::readOverReflection($reader, UserStub::class);

        self::assertType('positive-int', self::getProperty($first, 'id')->read);
        self::assertType('positive-int', self::getProperty($second, 'id')->read);
    }

    public function testClassIsFoundRegardlessOfCaseAndLeadingBackslash(): void
    {
        $key = '\\' . \strtoupper(UserStub::class);
        $reader = new ArrayMetadataReader([$key => ['properties' => ['id' => 'int']]]);

        $id = self::getProperty(self::readOverReflection($reader, UserStub::class), 'id');

        self::assertType('int', $id->read);
        self::assertSame('$[\'' . $key . '\'].properties.id', $id->read->source->path);
    }

    public function testClassWithoutConfigIsLeftAsIs(): void
    {
        $reader = new JsonFileMetadataReader(self::FIXTURES . 'config.json');

        self::assertSame([], self::readAsFirst($reader, PromotedStub::class)->properties);
    }

    public function testErrorsAreThrownTogetherWithFileAndPathInConfig(): void
    {
        $reader = new JsonFileMetadataReader(self::FIXTURES . 'errors.json');
        $read = static fn() => self::readOverReflection($reader, UserStub::class);
        $class = UserStub::class;
        $at = "in errors.json at \$['{$class}']";

        self::assertSame([
            UnknownPropertyException::class,
            UnknownPropertyException::class,
            InvalidOptionValueException::class,
            UnknownKeyException::class,
            InvalidOptionValueException::class,
            UnknownKeyException::class,
        ], \array_map(static fn(\Throwable $error): string => $error::class, self::getExceptions($read)));
        self::assertSame([
            "{$class}::\$missing: property does not exist {$at}.properties.missing",
            "{$class}::\$counter: property is not mapped {$at}.properties.counter",
            "{$class}::\$id: value of \"name\" must be a non-empty string {$at}.properties.id.name",
            "{$class}::\$id: unknown key \"size\" {$at}.properties.id.size",
            "{$class}::\$email: config must be a string or a map {$at}.properties.email",
            "{$class}: unknown key \"colour\" {$at}.colour",
        ], self::getErrors($read));
    }

    public function testObjectDefaultValueIsThrownAndEnumCaseIsSet(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => [
                'properties' => [
                    'email' => ['default' => new \stdClass()],
                    'displayName' => ['default' => [StatusStub::Active]],
                ],
            ],
        ]);
        $read = static fn() => self::readOverReflection($reader, UserStub::class);
        $class = UserStub::class;

        self::assertContainsOnlyInstancesOf(InvalidDefaultValueException::class, self::getExceptions($read));
        self::assertSame([
            "{$class}::\$email: default value of type \"stdClass\" is not supported in \$['{$class}'].properties.email.default",
        ], self::getErrors($read));

        try {
            $read();
        } catch (InvalidMetadataException $e) {
            self::assertNull(self::getProperty($e->metadata, 'email')->default?->value);
            self::assertSame([StatusStub::Active], self::getProperty($e->metadata, 'displayName')->default?->value);
        }
    }

    public function testTypesAreParsedByGivenParser(): void
    {
        $parser = new TypeParser(new TypeParserFeatures(unions: false));
        $reader = new ArrayMetadataReader([UserStub::class => ['properties' => ['id' => 'int|string']]], null, $parser);

        $id = self::getProperty(self::readOverReflection($reader, UserStub::class), 'id');

        self::assertNotNull($id->read?->error);
    }

    public function testUnparsableTypeIsTypeWithErrorAndIsNotThrown(): void
    {
        $reader = new ArrayMetadataReader([UserStub::class => ['properties' => ['friend' => ['type' => 'array<']]]]);

        $friend = self::getProperty(self::readOverReflection($reader, UserStub::class), 'friend');

        self::assertNotNull($friend->read?->error);
        self::assertSame('$[\'' . UserStub::class . '\'].properties.friend.type', $friend->read->source->path);
    }

    public function testMessageAndFormKeysAreRead(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => [
                'type_error_message' => 'User is expected',
                'normalize_as_array' => false,
                'properties' => [
                    'id' => ['type_error_message' => 'Id is a number', 'undefined_error_message' => 'Id is required'],
                ],
            ],
        ]);

        $metadata = self::readOverReflection($reader, UserStub::class);
        $id = self::getProperty($metadata, 'id');

        self::assertSame('User is expected', $metadata->typeErrorMessage);
        self::assertFalse($metadata->isNormalizeAsArray);
        self::assertSame('Id is a number', $id->typeErrorMessage);
        self::assertSame('Id is required', $id->undefinedErrorMessage);
    }

    public function testAliasesKeyIsRead(): void
    {
        $reader = new ArrayMetadataReader([
            StatusStub::class => ['aliases' => ['on' => 'active', '10' => 'inactive']],
        ]);

        self::assertSame(
            ['on' => 'active', '10' => 'inactive'],
            self::readOverReflection($reader, StatusStub::class)->aliases,
        );
    }

    public function testWrongAliasesKeyIsThrown(): void
    {
        $class = StatusStub::class;
        $path = " in \$['{$class}'].aliases";

        self::assertSame(
            ["{$class}: value of \"aliases\" must be a map{$path}"],
            self::getErrors(static fn() => self::readOverReflection(
                new ArrayMetadataReader([$class => ['aliases' => 'on']]),
                $class,
            )),
        );
        self::assertSame(
            [
                "{$class}: value of \"aliases.on\" must be an integer or a string{$path}.on",
                "{$class}: value \"deleted\" is not a value of a case{$path}.off",
            ],
            self::getErrors(static fn() => self::readOverReflection(
                new ArrayMetadataReader([$class => ['aliases' => ['on' => [], 'off' => 'deleted']]]),
                $class,
            )),
        );
    }

    public function testNamingStrategyKeyIsRead(): void
    {
        $reader = new ArrayMetadataReader([UserStub::class => ['naming_strategy' => 'kebab-case']]);

        self::assertInstanceOf(
            KebabCaseNamingStrategy::class,
            self::readOverReflection($reader, UserStub::class)->namingStrategy,
        );

        $reader = new ArrayMetadataReader([UserStub::class => ['naming_strategy' => SnakeCaseNamingStrategy::class]]);

        self::assertInstanceOf(
            SnakeCaseNamingStrategy::class,
            self::readOverReflection($reader, UserStub::class)->namingStrategy,
        );
    }

    public function testWrongNamingStrategyKeyIsThrown(): void
    {
        $class = UserStub::class;
        $source = " in \$['{$class}'].naming_strategy";

        self::assertSame(
            ["{$class}: value of \"naming_strategy\" must be a non-empty string{$source}"],
            self::getErrors(static fn() => self::readOverReflection(
                new ArrayMetadataReader([$class => ['naming_strategy' => 42]]),
                $class,
            )),
        );
        self::assertSame(
            [
                "{$class}: naming strategy \"" . PrefixNamingStrategyStub::class . '" requires constructor'
                    . " arguments, pass its instance to the attribute{$source}",
            ],
            self::getErrors(static fn() => self::readOverReflection(
                new ArrayMetadataReader([$class => ['naming_strategy' => PrefixNamingStrategyStub::class]]),
                $class,
            )),
        );
        self::assertSame(
            [
                "{$class}: naming strategy \"" . NamingCaseStub::class . '" is neither a built-in one nor an'
                    . ' instantiable class of "' . NamingStrategyInterface::class . '"' . $source,
            ],
            self::getErrors(static fn() => self::readOverReflection(
                new ArrayMetadataReader([$class => ['naming_strategy' => NamingCaseStub::class]]),
                $class,
            )),
        );
    }

    public function testWrongMessageAndFormKeysAreThrown(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => [
                'type_error_message' => '',
                'normalize_as_array' => 'no',
                'properties' => ['id' => ['undefined_error_message' => 42]],
            ],
        ]);
        $class = UserStub::class;

        self::assertSame(
            [
                "{$class}::\$id: value of \"undefined_error_message\" must be a non-empty string"
                    . " in \$['{$class}'].properties.id.undefined_error_message",
                "{$class}: value of \"type_error_message\" must be a non-empty string"
                    . " in \$['{$class}'].type_error_message",
                "{$class}: value of \"normalize_as_array\" must be a boolean in \$['{$class}'].normalize_as_array",
            ],
            self::getErrors(static fn() => self::readOverReflection($reader, UserStub::class)),
        );
    }

    public function testSkipConfigSetsConditions(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => [
                'properties' => [
                    'id' => ['skip' => 'null'],
                    'email' => ['skip' => ['empty', 'this.id > 10']],
                ],
            ],
        ]);

        $metadata = self::readOverReflection($reader, UserStub::class);

        $id = self::getProperty($metadata, 'id')->skip;
        self::assertCount(1, $id);
        self::assertInstanceOf(NullSkipCondition::class, $id[0]);

        $email = self::getProperty($metadata, 'email')->skip;
        self::assertCount(2, $email);
        self::assertInstanceOf(EmptySkipCondition::class, $email[0]);
        self::assertInstanceOf(ExpressionSkipCondition::class, $email[1]);
        self::assertSame('this', $email[1]->variable);
    }

    public function testWrongSkipConfigIsThrown(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => ['properties' => ['id' => ['skip' => [42]], 'email' => ['skip' => 'this.']]],
        ]);
        $class = UserStub::class;

        $errors = self::getErrors(static fn() => self::readOverReflection($reader, UserStub::class));

        self::assertCount(2, $errors);
        self::assertSame(
            "{$class}::\$id: value of \"skip\" must be a non-empty string in \$['{$class}'].properties.id.skip",
            $errors[0],
        );
        self::assertStringStartsWith("{$class}::\$email: expression \"this.\" is invalid (", $errors[1]);
    }

    public function testDiscriminatorConfigSetsPolymorphism(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => [
                'discriminator' => [
                    'field' => 'kind',
                    'map' => ['promoted' => PromotedStub::class, 7 => 'int'],
                    'otherwise' => StatusStub::class,
                ],
            ],
        ]);

        $discriminator = self::readOverReflection($reader, UserStub::class)->discriminator;

        self::assertNotNull($discriminator);
        self::assertSame('kind', $discriminator->field);
        self::assertType(PromotedStub::class, $discriminator->map['promoted']);
        self::assertType('int', $discriminator->map[7]);
        self::assertType(StatusStub::class, $discriminator->default);
        self::assertSame('$[\'' . UserStub::class . '\'].discriminator', $discriminator->source->path);
    }

    public function testWrongDiscriminatorConfigIsThrown(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => ['discriminator' => ['field' => 'kind', 'map' => [], 'other' => 1]],
        ]);
        $class = UserStub::class;

        self::assertSame(
            [
                "{$class}: unknown key \"other\" in \$['{$class}'].discriminator.other",
                "{$class}: value of \"map\" must be a non-empty map in \$['{$class}'].discriminator.map",
            ],
            self::getErrors(static fn() => self::readOverReflection($reader, UserStub::class)),
        );
    }

    public function testUnreadableTypeOfDiscriminatorIsThrownByValidation(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => ['discriminator' => ['field' => 'kind', 'map' => ['broken' => 'list<']]],
        ]);

        $errors = self::getErrors(static fn() => self::provide(UserStub::class, $reader, new ReflectionMetadataReader()));

        self::assertCount(1, $errors);
        self::assertStringStartsWith(UserStub::class . ': ', $errors[0]);
        self::assertStringEndsWith(' in $[\'' . UserStub::class . '\'].discriminator', $errors[0]);
    }

    public function testConfigOfPropertyThatIsNotMappedIsThrown(): void
    {
        $reader = new ArrayMetadataReader([UserStub::class => ['properties' => ['secret' => 'non-empty-string']]]);
        $class = UserStub::class;

        $errors = self::getErrors(static fn() => self::readOverReflection($reader, UserStub::class));

        self::assertSame(["{$class}::\$secret: property is not mapped in \$['{$class}'].properties.secret"], $errors);
    }

    public function testFirstReaderMapsNoPropertiesSoEveryPropertyConfigIsThrown(): void
    {
        $reader = new ArrayMetadataReader([UserStub::class => ['properties' => ['id' => 'int']]]);
        $class = UserStub::class;

        $errors = self::getErrors(static fn() => self::readAsFirst($reader, UserStub::class));

        self::assertSame(["{$class}::\$id: property is not mapped in \$['{$class}'].properties.id"], $errors);
    }

    public function testConfigOverridesValuesOfLowerReaders(): void
    {
        $reader = new ArrayMetadataReader([
            UserStub::class => [
                'properties' => [
                    'id' => ['type' => 'int', 'name' => 'user_id', 'default' => 'config'],
                ],
            ],
        ]);

        $id = self::getProperty(self::readOverReflection($reader, UserStub::class), 'id');

        self::assertType('int', $id->read);
        self::assertSame('$[\'' . UserStub::class . '\'].properties.id.type', $id->read->source->path);
        self::assertSame('user_id', $id->externalName);
        self::assertSame('config', $id->default?->value);
    }

    public function testStrictnessWithReadTypeAppliesToReadTypeOnly(): void
    {
        $id = self::getProperty(self::readStrictness(['read_type' => 'positive-int', 'strict' => true]), 'id');

        self::assertType('positive-int', $id->read);
        self::assertTrue($id->read->isStrict);
        self::assertType('int', $id->write);
        self::assertNull($id->write->source->path);
        self::assertNull($id->write->isStrict);
    }

    public function testStrictnessWithWriteTypeAppliesToWriteTypeOnly(): void
    {
        $id = self::getProperty(self::readStrictness(['write_type' => 'positive-int', 'strict' => true]), 'id');

        self::assertType('positive-int', $id->write);
        self::assertTrue($id->write->isStrict);
        self::assertType('int', $id->read);
        self::assertNull($id->read->source->path);
        self::assertNull($id->read->isStrict);
    }

    public function testStrictnessWithTypeAppliesToBothTypes(): void
    {
        $id = self::getProperty(self::readStrictness(['type' => 'positive-int', 'strict' => true]), 'id');

        self::assertType('positive-int', $id->read);
        self::assertType('positive-int', $id->write);
        self::assertTrue($id->read->isStrict);
        self::assertTrue($id->write->isStrict);
    }

    public function testStrictnessWithoutTypeAppliesToLowerTypes(): void
    {
        $id = self::getProperty(self::readStrictness(['strict' => true]), 'id');

        self::assertType('int', $id->read);
        self::assertType('int', $id->write);
        self::assertNull($id->read->source->path);
        self::assertTrue($id->read->isStrict);
        self::assertTrue($id->write->isStrict);
    }

    /**
     * @return iterable<non-empty-string, array{
     *     MetadataReaderInterface,
     *     non-empty-string,
     * }>
     */
    public static function brokenConfigDataProvider(): iterable
    {
        yield 'missing file' => [
            new JsonFileMetadataReader(self::FIXTURES . 'missing.json'),
            'File "' . self::FIXTURES . 'missing.json" cannot be read',
        ];
        yield 'invalid json' => [
            new JsonFileMetadataReader(self::FIXTURES . 'invalid.json'),
            'File "' . self::FIXTURES . 'invalid.json" cannot be decoded: Syntax error',
        ];
        yield 'yaml tag' => [
            new YamlFileMetadataReader(self::FIXTURES . 'tagged.yaml'),
            'File "' . self::FIXTURES . 'tagged.yaml" cannot be decoded: The string "!php/const PHP_VERSION"',
        ];
        yield 'not a map of classes' => [
            new ArrayMetadataReader(['int']),
            'Key "0" of config is not a class name',
        ];
        yield 'class in two spellings' => [
            new ArrayMetadataReader([UserStub::class => [], '\\' . UserStub::class => []]),
            'Class "\\' . UserStub::class . '" of config is described more than once',
        ];
    }

    /**
     * @param non-empty-string $message
     */
    #[DataProvider('brokenConfigDataProvider')]
    public function testConfigThatCannotBeLoadedIsRejectedOnRead(MetadataReaderInterface $reader, string $message): void
    {
        $this->expectException(ConfigLoadingException::class);
        $this->expectExceptionMessage($message);

        self::readAsFirst($reader, PromotedStub::class);
    }

    /**
     * Reads {@see UserStub} with the given config of its `$id` property.
     *
     * @param array<non-empty-string, mixed> $id
     * @return ClassMetadata<UserStub>
     * @throws \Throwable
     */
    private static function readStrictness(array $id): ClassMetadata
    {
        $reader = new ArrayMetadataReader([UserStub::class => ['properties' => ['id' => $id]]]);

        return self::readOverReflection($reader, UserStub::class);
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function requireArray(string $file): array
    {
        $result = require $file;

        self::assertIsArray($result);

        return $result;
    }

    private static function printFile(?string $file): ?string
    {
        if ($file === null) {
            return null;
        }

        return \basename($file);
    }
}
