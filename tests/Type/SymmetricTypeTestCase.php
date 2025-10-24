<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

abstract class SymmetricTypeTestCase extends TypeTestCase
{
    /**
     * @return iterable<mixed, bool>
     */
    abstract protected static function matchValues(bool $strict): iterable;

    protected static function matchNormalizationValues(bool $strict): iterable
    {
        return static::matchValues($strict);
    }

    protected static function matchDenormalizationValues(bool $strict): iterable
    {
        return static::matchValues($strict);
    }

    /**
     * @return iterable<mixed, mixed>
     */
    abstract protected static function castValues(bool $strict): iterable;

    protected static function castNormalizationValues(bool $strict): iterable
    {
        return static::castValues($strict);
    }

    protected static function castDenormalizationValues(bool $strict): iterable
    {
        return static::castValues($strict);
    }
}
