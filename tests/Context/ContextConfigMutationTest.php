<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Path\Entry\ObjectEntry;

#[Group('context')]
final class ContextConfigMutationTest extends ContextTestCase
{
    public function testOverrideConfig(): void
    {
        $context = self::createNormalizationContext(42);

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->config->withStrictTypes(false));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());
    }

    public function testConfigRollbackAfterEntrance(): void
    {
        $context = self::createNormalizationContext(42);

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->config->withStrictTypes(false));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'));

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());
    }

    public function testConfigRollbackAfterMultipleEntrance(): void
    {
        $context = self::createNormalizationContext(42);

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->config->withStrictTypes(false));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->config->withStrictTypes(false));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->config->withStrictTypes(false));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'));

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());
    }

    public function testNonDefaultConfigRollbackAfterMultipleEntrance(): void
    {
        $context = self::createNormalizationContext(42, new Configuration(
            objectAsArray: false,
            strictTypes: false,
        ));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertFalse($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->config->withStrictTypes(true));

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertFalse($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->config->withObjectAsArray(true));

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertFalse($context->config->isObjectAsArray());
    }

    public function testOnlyOneConfigChanged(): void
    {
        $context = self::createNormalizationContext(42, new Configuration(
            objectAsArray: false,
            strictTypes: false,
        ));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertFalse($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->withStrictTypes(true));

        self::assertTrue($context->config->isStrictTypesEnabled());
        self::assertFalse($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'),
            $context->withObjectAsArray(true));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertTrue($context->config->isObjectAsArray());

        $context = $context->enter(42, new ObjectEntry('object'));

        self::assertFalse($context->config->isStrictTypesEnabled());
        self::assertFalse($context->config->isObjectAsArray());
    }
}
