<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Provider\Cache;

use TypeLang\Mapper\Mapping\Provider\InMemoryMetadataProvider;
use TypeLang\Mapper\Mapping\Tests\Stub\CountingMetadataProvider;
use TypeLang\Mapper\Mapping\Tests\Stub\ReaderExceptionStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;

final class InMemoryMetadataProviderTest extends TestCase
{
    public function testSameInstanceIsReturnedForClass(): void
    {
        $delegate = new CountingMetadataProvider();
        $provider = new InMemoryMetadataProvider($delegate);

        $first = $provider->read(new \ReflectionClass(\stdClass::class));
        $second = $provider->read(new \ReflectionClass(\stdClass::class));

        self::assertSame($first, $second);
        self::assertSame(1, $delegate->calls);
    }

    public function testDelegateIsCalledOncePerClass(): void
    {
        $delegate = new CountingMetadataProvider();
        $provider = new InMemoryMetadataProvider($delegate);

        $provider->read(new \ReflectionClass(\stdClass::class));
        $provider->read(new \ReflectionClass(\ArrayObject::class));
        $provider->read(new \ReflectionClass(\stdClass::class));
        $metadata = $provider->read(new \ReflectionClass(\ArrayObject::class));

        self::assertSame(\ArrayObject::class, $metadata->name);
        self::assertSame(2, $delegate->calls);
    }

    public function testDelegateExceptionIsNotRemembered(): void
    {
        $delegate = new CountingMetadataProvider(new ReaderExceptionStub());
        $provider = new InMemoryMetadataProvider($delegate);

        $failures = 0;

        for ($i = 0; $i < 2; ++$i) {
            try {
                $provider->read(new \ReflectionClass(\stdClass::class));
            } catch (ReaderExceptionStub) {
                ++$failures;
            }
        }

        self::assertSame(2, $failures);
        self::assertSame(2, $delegate->calls);
    }
}
