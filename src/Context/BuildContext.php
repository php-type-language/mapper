<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Printer\TypePrinterInterface;

/**
 * Everything the builders need to create a type, used once per type.
 */
final class BuildContext
{
    public function __construct(
        public readonly TypeRepositoryInterface $types,
        public readonly MetadataProviderInterface $metadata,
        /**
         * Runtime side of the classes, the same object as
         * {@see Context::$accessors}
         */
        public readonly ClassAccessors $accessors,
        /**
         * Prints the canonical description of a type in one line, such as
         * for the type in errors of its definition
         */
        public readonly TypePrinterInterface $printer,
    ) {}
}
