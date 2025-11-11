<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

trait InteractWithTypeRepository
{
    use InteractWithPlatform;
    use InteractWithTypeParser;

    /**
     * @var \WeakMap<Direction, TypeRepositoryInterface>
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

    private static function createTypeRepository(Direction $direction): TypeRepositoryInterface
    {
        $platform = self::getPlatform();

        return new TypeRepository(
            parser: self::getTypeParser(),
            builders: $platform->getTypes($direction)
        );
    }

    protected static function getTypeRepository(Direction $direction): TypeRepositoryInterface
    {
        return self::$currentTypeRepository[$direction] ??= self::createTypeRepository($direction);
    }
}
