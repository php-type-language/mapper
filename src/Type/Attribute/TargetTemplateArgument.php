<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TargetTemplateArgument extends InjectTarget
{
    /**
     * @var list<non-empty-string>
     */
    public readonly array $allowedIdentifiers;

    /**
     * @param iterable<array-key, non-empty-string>|non-empty-string $allowedIdentifiers
     */
    public function __construct(
        iterable|string $allowedIdentifiers = [],
    ) {
        $this->allowedIdentifiers = \is_string($allowedIdentifiers)
            ? [$allowedIdentifiers]
            : \array_values([...$allowedIdentifiers]);

        parent::__construct(Target::TemplateArgument);
    }
}
