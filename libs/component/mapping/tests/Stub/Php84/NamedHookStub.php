<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;

/**
 * Field of a hook and of a getter sharing a name.
 */
final class NamedHookStub
{
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
