<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Stub;

use JMS\Serializer\Annotation\Type;
use TypeLang\Mapper\Mapping\MapType;

final class ExampleRequestDTO
{
    public function __construct(
        public readonly string $name,
        /**
         * @var list<ExampleRequestDTO>
         */
        #[MapType('list<ExampleRequestDTO>')]
        #[Type('array<TypeLang\Mapper\Bench\Stub\ExampleRequestDTO>')]
        public readonly array $items = [],
    ) {}
}
