<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Concerns;

trait InteractWithConcerns
{
    /**
     * @return list<trait-string>
     */
    protected static function getConcerns(): array
    {
        $result = [];

        $context = new \ReflectionClass(static::class);

        do {
            foreach ($context->getTraitNames() as $trait) {
                $result[] = $trait;
            }
        } while ($context = $context->getParentClass());

        return $result;
    }

    /**
     * @param trait-string $expected
     */
    protected static function hasConcern(string $expected): bool
    {
        return \in_array($expected, self::getConcerns(), true);
    }
}
