<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\PhpDocReader;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

abstract class HookTypePropertyPhpDocLoader extends PropertyPhpDocLoader
{
    protected static function isHooksSupported(): bool
    {
        return \PHP_VERSION_ID >= 80400;
    }

    protected function getSourceInfo(?\ReflectionMethod $hook): ?SourceInfo
    {
        if ($hook === null) {
            return null;
        }

        $file = $hook->getFileName();
        $line = $hook->getStartLine();

        if (\is_string($file) && $file !== '' && $line > 0) {
            return new SourceInfo($file, $line);
        }

        return null;
    }
}
