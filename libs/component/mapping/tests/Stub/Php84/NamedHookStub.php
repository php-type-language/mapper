<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;

final class NamedHookStub
{
    public string $code = '' {
        #[MapGetter('code')]
        get => $this->code;
    }

    public string $title = '' {
        #[MapGetter]
        get => $this->title;
    }

    #[MapGetter('title')]
    public function getTitle(): string
    {
        return $this->title;
    }
}
