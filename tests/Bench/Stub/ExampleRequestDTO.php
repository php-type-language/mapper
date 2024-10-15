<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Bench\Stub;

use JMS\Serializer\Annotation\Type;
use TypeLang\Mapper\Mapping\MapProperty;

final readonly class ExampleRequestDTO
{
    public function __construct(
        public string $name,
        /**
         * @var list<ExampleRequestDTO>
         */
        #[MapProperty('list<ExampleRequestDTO>')]
        #[Type('array<TypeLang\Mapper\Tests\Bench\Stub\ExampleRequestDTO>')]
        public array $items = [],
    ) {}
}
