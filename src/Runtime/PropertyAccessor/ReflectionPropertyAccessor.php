<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\PropertyAccessor;

final class ReflectionPropertyAccessor implements PropertyAccessorInterface
{
    /**
     * @param non-empty-string $property
     *
     * @throws \ReflectionException
     */
    private function getPropertyForGet(object $object, string $property): \ReflectionProperty
    {
        return new \ReflectionProperty($object, $property);
    }

    /**
     * @param non-empty-string $property
     *
     * @throws \ReflectionException
     */
    private function getPropertyForSet(object $object, string $property): \ReflectionProperty
    {
        $reflection = new \ReflectionProperty($object, $property);

        $context = $reflection->getDeclaringClass();

        return $context->getProperty($property);
    }

    public function getValue(object $object, string $property): mixed
    {
        try {
            $reflection = $this->getPropertyForGet($object, $property);

            return $reflection->getValue($object);
        } catch (\ReflectionException) {
            return null;
        }
    }

    public function isReadable(object $object, string $property): bool
    {
        if (!\property_exists($object, $property)) {
            return false;
        }

        if (\PHP_VERSION_ID >= 80400) {
            try {
                return $this->isReadableUsingHooks($object, $property);
            } catch (\ReflectionException) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param non-empty-string $property
     *
     * @throws \ReflectionException
     */
    private function isReadableUsingHooks(object $object, string $property): bool
    {
        $reflection = $this->getPropertyForSet($object, $property);

        // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
        return $reflection->getHook(\PropertyHookType::Get) !== null
            // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
            || $reflection->getHook(\PropertyHookType::Set) === null;
    }

    public function setValue(object $object, string $property, mixed $value): void
    {
        try {
            $reflection = $this->getPropertyForSet($object, $property);

            $reflection->setValue($object, $value);
        } catch (\ReflectionException) {
            return;
        }
    }

    public function isWritable(object $object, string $property): bool
    {
        if (\PHP_VERSION_ID >= 80400) {
            try {
                return $this->isWritableUsingHooks($object, $property);
            } catch (\ReflectionException) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param non-empty-string $property
     *
     * @throws \ReflectionException
     */
    private function isWritableUsingHooks(object $object, string $property): bool
    {
        $reflection = $this->getPropertyForSet($object, $property);

        // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
        return $reflection->getHook(\PropertyHookType::Get) === null
            // @phpstan-ignore-next-line : Requires PHPStan-compatible version for PHP 8.4
            || $reflection->getHook(\PropertyHookType::Set) !== null;
    }
}
