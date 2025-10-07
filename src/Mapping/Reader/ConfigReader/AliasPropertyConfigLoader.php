<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\PropertyInfo;

final class AliasPropertyConfigLoader extends PropertyConfigLoader
{
    public function load(PropertyInfo $info, array $config): void
    {
        if (!isset($config['name'])) {
            return;
        }

        $info->alias = $config['name'];
    }
}
