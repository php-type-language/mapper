<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform;

enum GrammarFeature
{
    /**
     * Enables conditional types such as `T ? X : Y`.
     */
    case Conditional;

    /**
     * Enables type shapes such as `T{key: X}`.
     */
    case Shapes;

    /**
     * Enables callable types such as `name(X, Y): T`.
     */
    case Callables;

    /**
     * Enables literal types such as `42` or `"string"`.
     */
    case Literals;

    /**
     * Enables template arguments such as `T<X, Y>`.
     */
    case Generics;

    /**
     * Enables logical union types such as `T | X`.
     */
    case Union;

    /**
     * Enables logical intersection types such as `T & X`.
     */
    case Intersection;

    /**
     * Enables list types such as `T[]`.
     */
    case List;

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
