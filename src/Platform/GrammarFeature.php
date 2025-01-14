<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

enum GrammarFeature
{
    /**
     * Enables conditional types such as `T ? U : V`.
     */
    case Conditional;

    /**
     * Enables type shapes such as `T{key: U}`.
     */
    case Shapes;

    /**
     * Enables callable types such as `name(U, V): T`.
     */
    case Callables;

    /**
     * Enables literal types such as `42` or `"string"`.
     */
    case Literals;

    /**
     * Enables template arguments such as `T<U, V>`.
     */
    case Generics;

    /**
     * Enables logical union types such as `T | U`.
     */
    case Union;

    /**
     * Enables logical intersection types such as `T & U`.
     */
    case Intersection;

    /**
     * Enables list types such as `T[]`.
     */
    case List;

    /**
     * Enables offset types such as `T[U]`.
     */
    case Offsets;

    /**
     * Enables or disables support for template argument
     * hints such as `T<out U, in V>`.
     */
    case Hints;

    /**
     * Enables or disables support for attributes such as `#[attr]`.
     */
    case Attributes;
}
