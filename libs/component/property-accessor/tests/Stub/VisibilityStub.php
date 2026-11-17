<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

/**
 * Instance properties with every access modifier.
 */
final class VisibilityStub
{
    public string $publicProperty = 'public-value';

    protected string $protectedProperty = 'protected-value';

    private string $privateProperty = 'private-value';

    public function getPrivateProperty(): string
    {
        return $this->privateProperty;
    }

    public function getProtectedProperty(): string
    {
        return $this->protectedProperty;
    }
}
