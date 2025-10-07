<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

// Create standard platform with REFLECTION READER
$platform = new \TypeLang\Mapper\Platform\StandardPlatform(
    meta: new \TypeLang\Mapper\Mapping\Reader\PhpConfigReader(
        directories: __DIR__ . '/05.php-config-mapping',
        // 1) Read the metadata from the attributes
        delegate: new \TypeLang\Mapper\Mapping\Reader\AttributeReader(
            // 2) If there is no info, then use custom phpdoc tags
            delegate: new \TypeLang\Mapper\Mapping\Reader\PhpDocReader(
                paramTagName: 'map-param',
                varTagName: 'map-var',
                returnTagName: 'map-return',
                // 3) If there is no info, then use default phpdoc tags
                delegate: new \TypeLang\Mapper\Mapping\Reader\PhpDocReader(
                    // 4) If there is no info, then use native type info
                    delegate: new \TypeLang\Mapper\Mapping\Reader\ReflectionReader(
                        // 5) If there is no info, the metadata will be empty
                        delegate: new \TypeLang\Mapper\Mapping\Reader\NullReader(),
                    ),
                )
            ),
        ),
    )
);

$mapper = new \TypeLang\Mapper\Mapper($platform);
