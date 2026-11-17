<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Request;

/**
 * Controller argument denormalized from the data of the request by a
 * mapper and validated, in case of `symfony/validator` is installed.
 */
abstract class MapRequest
{
    public function __construct(
        /**
         * Type of the argument as written in PHPDoc, such as
         * `list<App\Dto\Item>`, the declared type of the argument in case
         * of {@see null}
         *
         * @var non-empty-string|null
         */
        public readonly ?string $type = null,
        /**
         * Name of the mapper, the default one in case of {@see null}
         *
         * @var non-empty-string|null
         */
        public readonly ?string $mapper = null,
        public readonly bool $isValidated = true,
        /**
         * Validation groups, the default group in case of {@see null}
         *
         * @var list<non-empty-string>|null
         */
        public readonly ?array $validationGroups = null,
    ) {}
}
