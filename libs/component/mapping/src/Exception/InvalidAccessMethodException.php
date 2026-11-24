<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

use TypeLang\Mapper\Mapping\Metadata\SourceInfo;

/**
 * Method declared as a getter or a setter cannot be one.
 */
final class InvalidAccessMethodException extends MetadataException
{
    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseMethodIsNotPublic(string $method, SourceInfo $source): self
    {
        return new self($method, $source, 'method is not public');
    }

    /**
     * @param string $method method reference: `App\User::__construct()`
     */
    public static function becauseMethodIsMagic(string $method, SourceInfo $source): self
    {
        return new self($method, $source, 'method is the constructor or a magic method');
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseMethodIsStatic(string $method, SourceInfo $source): self
    {
        return new self($method, $source, 'method is static');
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     */
    public static function becauseGetterRequiresArguments(string $method, SourceInfo $source): self
    {
        return new self($method, $source, 'method requires arguments, a getter takes none');
    }

    /**
     * @param string $method method reference: `App\User::getId()`
     * @param 'void'|'never' $type
     */
    public static function becauseGetterReturnsNothing(string $method, string $type, SourceInfo $source): self
    {
        $message = \sprintf('method returns %s, a getter returns a value', $type);

        return new self($method, $source, $message);
    }

    /**
     * @param string $method method reference: `App\User::setId()`
     */
    public static function becauseSetterDoesNotTakeOneValue(string $method, SourceInfo $source): self
    {
        return new self($method, $source, 'method must take exactly one value as a setter');
    }
}
