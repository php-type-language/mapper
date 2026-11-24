<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Set hooks of a property initialized by PHP and of a promoted property
 * rejecting its default.
 */
final class DefaultHookStub
{
    public string $title = 'default' {
        #[MapSetter]
        set(string $value) {
            $this->title = 'set:' . $value;
        }
    }

    public function __construct(
        public string $code = '' {
            #[MapSetter]
            set(string $value) {
                if ($value === '') {
                    throw new \InvalidArgumentException('Code is empty');
                }

                $this->code = $value;
            }
        },
    ) {}
}
