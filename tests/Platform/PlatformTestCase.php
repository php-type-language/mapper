<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Platform;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Tests\Platform\Stub\ExampleClassStub;
use TypeLang\Mapper\Tests\Platform\Stub\ExampleInterfaceStub;
use TypeLang\Mapper\Tests\Platform\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Platform\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Tests\Platform\Stub\UnitEnumStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Parser\Exception\ParseException;

abstract class PlatformTestCase extends TestCase
{
    // v2.1
    protected const TYPES_PHPSTAN = [
        'int',
        'integer',
        'positive-int',
        'negative-int',
        'non-positive-int',
        'non-negative-int',
        'non-zero-int',
        'string',
        'lowercase-string',
        'uppercase-string',
        'literal-string',
        'class-string',
        'interface-string',
        'trait-string',
        'enum-string',
        'callable-string',
        'array-key',
        'scalar',
        'empty-scalar',
        'non-empty-scalar',
        'number',
        'numeric',
        'numeric-string',
        'non-empty-string',
        'non-empty-lowercase-string',
        'non-empty-uppercase-string',
        'truthy-string',
        'non-falsy-string',
        'non-empty-literal-string',
        'bool',
        'boolean',
        'true',
        'false',
        'null',
        'float',
        'double',
        'array',
        'associative-array',
        'non-empty-array',
        'iterable',
        'callable',
        'pure-callable',
        'pure-closure',
        'resource',
        'open-resource',
        'closed-resource',
        'mixed',
        'non-empty-mixed',
        'void',
        'object',
        'callable-object',
        'callable-array',
        'never',
        'noreturn',
        'never-return',
        'never-returns',
        'no-return',
        'list',
        'non-empty-list',
        'empty',
        '__stringandstringable',
        'self',
        'static',
        'parent',
        'key-of',
        'value-of',
        'int-mask-of',
        'int-mask',
        '__benevolent',
        'template-type',
        'new',
    ];

    protected const TYPES_PHAN = [
        'integer',
        'string',
        'double',
        'object',
        'boolean',
        'array',
        'iterable',
        'array-key',
        'bool',
        'false',
        'float',
        'int',
        'mixed',
        'null',
        'true',
        'class-string',
        'associative-array',
        'non-empty-associative-array',
        'non-empty-array',
        'non-empty-list',
        'non-empty-string',
        'non-empty-lowercase-string',
        'non-zero-int',
        'resource',
        'callable',
        'callable-array',
        'callable-object',
        'callable-string',
        'closure',
        'phan-intersection-type',
        'non-empty-mixed',
        'non-null-mixed',
        'scalar',
        'lowercase-string',
        'numeric-string',
        'void',
        'never',
        'no-return',
        'never-return',
        'never-returns',
        'static',
        '$this',
    ];

    protected const TYPES_PSALM = [
        'int',
        'float',
        'string',
        'bool',
        'void',
        'array-key',
        'iterable',
        'never',
        'never-return',
        'never-returns',
        'no-return',
        'empty',
        'object',
        'callable',
        'pure-callable',
        'array',
        'associative-array',
        'non-empty-array',
        'callable-array',
        'list',
        'non-empty-list',
        'non-empty-string',
        'truthy-string',
        'non-falsy-string',
        'lowercase-string',
        'non-empty-lowercase-string',
        'resource',
        'resource (closed)',
        'closed-resource',
        'positive-int',
        'non-positive-int',
        'negative-int',
        'non-negative-int',
        'numeric',
        'true',
        'false',
        'scalar',
        'null',
        'mixed',
        'callable-object',
        'stringable-object',
        'class-string',
        'interface-string',
        'enum-string',
        'trait-string',
        'callable-string',
        'numeric-string',
        'literal-string',
        'non-empty-literal-string',
        'literal-int',
        '$this',
        'non-empty-scalar',
        'empty-scalar',
        'non-empty-mixed',
        'Closure',
        'traversable',
        'countable',
        'arrayaccess',
        'pure-closure',
        'boolean',
        'integer',
        'double',
        'real',
        'self',
        'static',
        'key-of',
        'value-of',
        'properties-of',
        'public-properties-of',
        'protected-properties-of',
        'private-properties-of',
        'non-empty-countable',
        'class-string-map',
        'open-resource',
        'arraylike-object',
        'int-mask',
        'int-mask-of',
    ];

    protected const TYPES_PHP = [
        // TODO: Self references not supported yet
        'self',
        // TODO: Parent references not supported yet
        'parent',
        'array',
        ExampleClassStub::class,
        // TODO: Interfaces not supported yet
        ExampleInterfaceStub::class,
        // TODO: Callable types not supported yet
        'callable',
        'int',
        'integer',
        'bool',
        'boolean',
        'float',
        'real',
        'double',
        'string',
        // TODO: Void types not supported yet
        'void',
        '?int',
        'iterable',
        'object',
        'int|false',
        // TODO: Static references not supported yet
        'static',
        'mixed',
        // TODO: Void types not supported yet
        'never',
        // TODO: Intersection types not supported yet
        'int&string',
        UnitEnumStub::class,
        IntBackedEnumStub::class,
        StringBackedEnumStub::class,
        'null',
        'false',
        'true',
        // TODO: Intersection types not supported yet
        'int|(true&int)',
    ];

    public static function typesDataProvider(): iterable
    {
        foreach (self::TYPES_PHPSTAN as $type) {
            yield 'PHPStan(' . $type . ')' => [$type, false];
        }

        foreach (self::TYPES_PHAN as $type) {
            yield 'Phan(' . $type . ')' => [$type, false];
        }

        foreach (self::TYPES_PSALM as $type) {
            yield 'Psalm(' . $type . ')' => [$type, false];
        }

        foreach (self::TYPES_PHP as $type) {
            yield 'PHP(' . $type . ')' => [$type, false];
        }
    }

    abstract protected function createTypePlatform(): PlatformInterface;

    private function testTypeIsAvailable(string $definition, bool $supports, Direction $direction): void
    {
        if (!$supports) {
            $this->expectException(ParseException::class);
            $this->expectExceptionMessage('not allowed');
        }

        $parser = self::getTypeParser();
        $platform = $this->createTypePlatform();
        $statement = $parser->getStatementByDefinition($definition);

        if (!$supports) {
            $this->expectException(TypeNotFoundException::class);
            $this->expectExceptionMessage(\sprintf('Type "%s" is not defined', $definition));
        } else {
            $this->expectNotToPerformAssertions();
        }

        $repository = new TypeRepository(
            parser: $parser,
            builders: $platform->getTypes($direction),
        );

        $repository->getTypeByStatement($statement);
    }

    #[DataProvider('typesDataProvider')]
    public function testNormalizationTypeIsAvailable(string $definition, bool $supports): void
    {
        $this->testTypeIsAvailable($definition, $supports, Direction::Normalize);
    }

    #[DataProvider('typesDataProvider')]
    public function testDenormalizationTypeIsAvailable(string $definition, bool $supports): void
    {
        $this->testTypeIsAvailable($definition, $supports, Direction::Denormalize);
    }
}
