<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader;

use TypeLang\Mapper\Tests\Mapping\MappingTestCase;

abstract class ReaderTestCase extends MappingTestCase
{
    /**
     * @return non-empty-string
     */
    protected function getConfigDirectory(?string $suffix = null): string
    {
        $result = __DIR__ . '/Config';

        if ($suffix !== null) {
            $result .= '/' . $suffix;
        }

        return $result;
    }
}
