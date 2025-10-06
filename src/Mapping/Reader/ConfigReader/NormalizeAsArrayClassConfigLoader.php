<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;

final class NormalizeAsArrayClassConfigLoader extends ClassConfigLoader
{
    public function load(ClassInfo $info, array $config): void
    {
        if (!isset($config['normalize_as_array'])) {
            return;
        }

        $info->isNormalizeAsArray = $config['normalize_as_array'];
    }
}
