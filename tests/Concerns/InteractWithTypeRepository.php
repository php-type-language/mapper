<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Kernel\Repository\TypeRepository;
use TypeLang\Mapper\Kernel\Repository\TypeRepositoryInterface;

trait InteractWithTypeRepository
{
    use InteractWithMapperContext;
    use InteractWithPlatform;

    /**
     * @var \WeakMap<DirectionInterface, TypeRepositoryInterface>
     */
    protected static \WeakMap $currentTypeRepository;

    #[Before]
    public function beforeInteractWithTypeRepository(): void
    {
        self::$currentTypeRepository = new \WeakMap();
    }

    protected static function withTypeRepository(TypeRepositoryInterface $repository): void
    {
        foreach (Direction::cases() as $direction) {
            self::$currentTypeRepository[$direction] = $repository;
        }
    }

    private static function createTypeRepository(DirectionInterface $direction): TypeRepositoryInterface
    {
        $platform = self::getPlatform();

        return new TypeRepository(
            context: self::createMapperContext(),
            direction: $direction,
            builders: $platform->getTypes($direction)
        );
    }

    protected static function getTypeRepository(DirectionInterface $direction): TypeRepositoryInterface
    {
        return self::$currentTypeRepository[$direction]
            ??= self::createTypeRepository($direction);
    }
}
