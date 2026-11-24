<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Fixture;

use TypeLang\Mapper\Mapping\Tests\Stub\UserStub;

return [
    UserStub::class => [
        'properties' => [
            'id' => 'positive-int',
            'email' => [
                'get' => [
                    'name' => 'e_mail',
                    'type' => 'string',
                ],
                'set' => [
                    'name' => 'e_mail',
                    'type' => 'non-empty-string',
                    'strict' => true,
                    'default' => 'unknown',
                ],
            ],
            'friend' => [
                'type' => 'self|null',
                'default' => null,
            ],
            'displayName' => [
                'get' => [],
            ],
        ],
        'methods' => [
            'setEmail' => [
                'set' => [
                    'name' => 'email_in',
                ],
            ],
        ],
    ],
];
