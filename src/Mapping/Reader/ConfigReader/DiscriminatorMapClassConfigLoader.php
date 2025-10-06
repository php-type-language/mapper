<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use TypeLang\Mapper\Mapping\Metadata\ClassInfo;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeInfo;

final class DiscriminatorMapClassConfigLoader extends ClassConfigLoader
{
    public function load(\ReflectionClass $class, ClassInfo $info, array $config): void
    {
        if (!isset($config['discriminator'])) {
            return;
        }

        $discriminatorConfig = $config['discriminator'];

        $map = [];

        foreach ($discriminatorConfig['map'] as $value => $type) {
            $map[$value] = new TypeInfo($type);
        }

        $default = null;

        if (isset($discriminatorConfig['otherwise'])) {
            $default = new TypeInfo($discriminatorConfig['otherwise']);
        }

        $info->discriminator = new DiscriminatorInfo(
            field: $discriminatorConfig['field'],
            map: $map,
            default: $default,
        );
    }
}
