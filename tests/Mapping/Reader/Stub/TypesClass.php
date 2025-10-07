<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

interface FirstInterface {}

interface SecondInterface {}

final class ImplementsBoth implements FirstInterface, SecondInterface {}

final class TypesClass
{
    public int $intProp;

    public ?string $nullableString;

    public string|int $unionProp;

    /** @var FirstInterface&SecondInterface */
    public FirstInterface&SecondInterface $intersectionProp;

    public ImplementsBoth $classProp;

    public string $withDefault = 'd';

    protected string $protectedProp;

    private string $privateProp;

    public static string $staticProp;
}


