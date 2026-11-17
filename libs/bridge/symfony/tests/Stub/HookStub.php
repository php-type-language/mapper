<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperHook;
use TypeLang\Mapper\Hook\ClassHookInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

#[AsMapperHook(mappers: ['api'])]
final class HookStub implements ClassHookInterface
{
    public function isSupported(ClassMetadata $metadata): bool
    {
        return false;
    }
}
