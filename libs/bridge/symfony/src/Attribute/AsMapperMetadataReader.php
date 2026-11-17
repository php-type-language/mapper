<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Attribute;

use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;

/**
 * Adds the autoconfigured {@see MetadataReaderInterface} service to mappers,
 * over the default readers.
 *
 * ```
 * #[AsMapperMetadataReader]
 * final class DoctrineMetadataReader implements MetadataReaderInterface {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsMapperMetadataReader extends MapperExtensionAttribute {}
