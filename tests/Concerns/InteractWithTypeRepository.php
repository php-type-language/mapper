<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Kernel\Repository\TypeRepository;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;

trait InteractWithTypeRepository
{
    use InteractWithMapperContext;
    use InteractWithPlatform;

    protected static ?TypeRepositoryInterface $currentTypeRepository = null;

    #[Before]
    public function beforeInteractWithTypeRepository(): void
    {
        self::$currentTypeRepository = null;
    }

    protected static function withTypeRepository(TypeRepositoryInterface $repository): void
    {
        self::$currentTypeRepository = $repository;
    }

    private static function createTypeRepository(DirectionInterface $direction): TypeRepositoryInterface
    {
        $platform = self::getPlatform();

        return new TypeRepository(
            context: self::createMapperContext(),
            direction: $direction,
            builders: $platform->getTypes()
        );
    }

    protected static function getTypeRepository(DirectionInterface $direction): TypeRepositoryInterface
    {
        return self::$currentTypeRepository
            ??= self::createTypeRepository($direction);
    }
}
