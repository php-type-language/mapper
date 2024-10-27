<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bench\Stub;

use JMS\Serializer\Annotation\Type;
use TypeLang\Mapper\Mapping\MapType;

final readonly class ExampleRequestDTO
{
    public function __construct(
        public string $name,
        /**
         * @var list<ExampleRequestDTO>
         */
        #[MapType('list<ExampleRequestDTO>')]
        #[Type('array<TypeLang\Mapper\Bench\Stub\ExampleRequestDTO>')]
        public array $items = [],
    ) {}
}
