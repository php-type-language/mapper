<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reference;

use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Mapping\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Mapper\Mapping\Reference\ReferencesResolver;
use TypeLang\Mapper\Tests\Mapping\Reference\Stub\SimpleClassStub;
use TypeLang\Parser\Node\FullQualifiedName;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

final class ReferencesResolverTest extends ReferenceTestCase
{
    private function getResolver(array $useStatements = []): ReferencesResolver
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);

        $reader->method('getUseStatements')
            ->willReturn($useStatements);

        return new ReferencesResolver($reader);
    }

    #[TestDox('if no "use" stmt, then the namespace must be same as the class')]
    public function testResolveWithSimpleNameInSameNamespace(): void
    {
        $needle = new NamedTypeNode('TestClass');
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver()
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TypeLang\Mapper\Tests\Mapping\Reference\Stub\TestClass', $result->name->toString());
    }

    public function testResolveWithUseStatement(): void
    {
        $needle = new NamedTypeNode('Any');
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver([
            'Any' => 'Some\\Any',
            'Example' => 'Some\\Any\\Test',
        ])
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('Some\\Any', $result->name->toString());
    }

    public function testResolveWithAliasedUseStatement(): void
    {
        $needle = new NamedTypeNode('Example');
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver([
            'Example' => 'Some\\Any\\Test',
        ])
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('Some\\Any\\Test', $result->name->toString());
    }

    public function testResolveWithNamespaceKeyword(): void
    {
        $needle = new NamedTypeNode('namespace\\TestClass');
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver()
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TypeLang\\Mapper\\Tests\\Mapping\\Reference\\Stub\\TestClass', $result->name->toString());
    }

    public function testResolveWithNamespaceKeywordInGlobalNamespace(): void
    {
        $needle = new NamedTypeNode('namespace\\TestClass');
        $haystack = new \ReflectionClass(\stdClass::class); // stdClass is in global namespace

        $result = $this->getResolver()
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TestClass', $result->name->toString());
    }

    public function testResolveWithFullyQualifiedName(): void
    {
        $needle = new NamedTypeNode(new FullQualifiedName('\\Some\\Fully\\Qualified\\Class'));
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver()
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('\\Some\\Fully\\Qualified\\Class', $result->name->toString());
    }

    public function testResolveWithComplexNamespaceKeyword(): void
    {
        $needle = new NamedTypeNode('namespace\\Sub\\Namespace\\TestClass');
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver()
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TypeLang\\Mapper\\Tests\\Mapping\\Reference\\Stub\\Sub\\Namespace\\TestClass', $result->name->toString());
    }

    public function testResolveWithMixedUseStatements(): void
    {
        $needle = new NamedTypeNode('GlobalClass');
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver([
            'Any' => 'Some\\Any',
            'Example' => 'Some\\Any\\Test',
            'GlobalClass', // No alias, just the class name
        ])
            ->resolve($needle, $haystack);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('GlobalClass', $result->name->toString());
    }

    public function testResolvePreservesOriginalStatementWhenNoResolutionNeeded(): void
    {
        $needle = new NamedTypeNode('\\Fully\\Qualified\\Class');
        $haystack = new \ReflectionClass(SimpleClassStub::class);

        $result = $this->getResolver()
            ->resolve($needle, $haystack);

        self::assertSame($needle, $result);
    }
}
