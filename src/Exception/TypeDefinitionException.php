<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

/**
 * The type cannot be built: it is unknown or declared wrong.
 */
final class TypeDefinitionException extends \LogicException implements
    MapperExceptionInterface,
    NotInterceptableExceptionInterface
{
    /**
     * @param string $type the type as written: `list<int`
     */
    public static function becauseTypeIsWrittenWrong(string $type, \Throwable $previous): self
    {
        return new self(
            message: \sprintf('Type "%s" is written wrong: %s', \addcslashes($type, '"'), $previous->getMessage()),
            previous: $previous,
        );
    }

    /**
     * @param string $type the type as written: `list<int, string>`
     */
    public static function becauseTypeIsNotSupported(string $type): self
    {
        return new self(\sprintf('Type "%s" is not supported', \addcslashes($type, '"')));
    }

    /**
     * @param string $type the type as written: `App\User`
     */
    public static function becauseTypeCannotBeBuilt(string $type, \Throwable $previous): self
    {
        return new self(
            message: \sprintf('Type "%s" cannot be built, %s', \addcslashes($type, '"'), \lcfirst($previous->getMessage())),
            previous: $previous,
        );
    }

    /**
     * @param string $type the type a builder returned as the type of itself
     */
    public static function becauseTypeRefersToItself(string $type): self
    {
        return new self(\sprintf('Type "%s" refers to itself', \addcslashes($type, '"')));
    }

    public static function becauseRecursiveTypeIsNotBuilt(): self
    {
        return new self('Recursive type is used before it is built');
    }

    /**
     * @param string $type the type as written: `int<1.5, 2>`
     * @param int<1, 2> $position
     * @param 'min'|'max' $open name of the open bound at the position
     */
    public static function becauseRangeBoundIsInvalid(string $type, int $position, string $open): self
    {
        return new self(\sprintf(
            'Template argument #%d of type "%s" is invalid: must be an integer or "%s"',
            $position,
            \addcslashes($type, '"'),
            $open,
        ));
    }

    /**
     * @param string $type the type as written: `DateTime<'Y', 'm'>`
     */
    public static function becauseDateFormatIsInvalid(string $type): self
    {
        return new self(\sprintf(
            'Template argument #1 of type "%s" is invalid: must be the only one, a non-empty string of the format',
            \addcslashes($type, '"'),
        ));
    }

    /**
     * @param string $type the type as written: `array{a: int, a: string}`
     */
    public static function becauseShapeKeyIsDuplicated(string $type, int|string $key): self
    {
        return new self(\sprintf(
            'Type "%s" declares the key "%s" more than once',
            \addcslashes($type, '"'),
            \addcslashes((string) $key, '"'),
        ));
    }

    /**
     * @param string $type the type as written: `array{Foo::BAR: int}`
     */
    public static function becauseShapeKeyIsInvalid(string $type): self
    {
        return new self(\sprintf(
            'Type "%s" has a key that is not a name or a non-empty literal',
            \addcslashes($type, '"'),
        ));
    }

    /**
     * @param string $type the type as written: `Foo::BAR_*`
     */
    public static function becauseConstantIsNotFound(string $type): self
    {
        return new self(\sprintf('Type "%s" refers to no constant', \addcslashes($type, '"')));
    }

    /**
     * @param string $type the type as written: `self::BAR`
     */
    public static function becauseConstantClassIsRelative(string $type): self
    {
        return new self(\sprintf(
            'Type "%s" refers to a class relative to the declaration, write the class by its name',
            \addcslashes($type, '"'),
        ));
    }

    /**
     * @param string $type the type as written: `Foo::LIST`
     */
    public static function becauseConstantValueIsNotSupported(string $type, string $kind): self
    {
        return new self(\sprintf(
            'Type "%s" refers to a constant of type "%s", only a scalar, "null" or a case of an enum is allowed',
            \addcslashes($type, '"'),
            \addcslashes($kind, '"'),
        ));
    }

    /**
     * @param string $type the type as written: `int-mask<1, 'a'>`
     */
    public static function becauseMaskValueIsInvalid(string $type): self
    {
        return new self(\sprintf(
            'Type "%s" must be made of non-negative integers, their constants or unions of them',
            \addcslashes($type, '"'),
        ));
    }

    /**
     * @param string $type the type as written: `int<10, 1>`
     */
    public static function becauseRangeIsEmpty(string $type): self
    {
        return new self(\sprintf(
            'Type "%s" is empty: the lower bound is greater than the upper one',
            \addcslashes($type, '"'),
        ));
    }

    /**
     * @param class-string $class
     * @param non-empty-string $error why the source cannot read the type
     */
    public static function becauseFieldTypeCannotBeRead(string $class, string $field, string $error): self
    {
        return new self(\sprintf(
            'Type of field "%s" of "%s" is invalid: %s',
            \addcslashes($field, '"'),
            \addcslashes($class, '"'),
            $error,
        ));
    }

    /**
     * @param class-string $class
     */
    public static function becauseSkipExpressionIsInvalid(
        string $class,
        string $field,
        string $expression,
        \Throwable $previous,
    ): self {
        return new self(\sprintf(
            'Skip expression "%s" of field "%s" of "%s" is invalid: %s',
            \addcslashes($expression, '"'),
            \addcslashes($field, '"'),
            \addcslashes($class, '"'),
            $previous->getMessage(),
        ), previous: $previous);
    }

    /**
     * @param class-string $class
     */
    public static function becauseFieldTypeIsNotDefined(string $class, string $field): self
    {
        return new self(\sprintf(
            'Type of field "%s" of "%s" is not defined',
            \addcslashes($field, '"'),
            \addcslashes($class, '"'),
        ));
    }

    /**
     * @param class-string $class
     * @param int|string|null $tag tag of the map, {@see null} for the type
     *        of the data with no known tag
     * @param non-empty-string $error why the source cannot read the type
     */
    public static function becauseDiscriminatorTypeCannotBeRead(
        string $class,
        int|string|null $tag,
        string $error,
    ): self {
        return new self(\sprintf('%s is invalid: %s', self::describeDiscriminator($class, $tag), $error));
    }

    /**
     * @param class-string $class
     * @param int|string|null $tag tag of the map, {@see null} for the type
     *        of the data with no known tag
     */
    public static function becauseDiscriminatorBaseCannotBeBuilt(string $class, int|string|null $tag): self
    {
        return new self(\sprintf(
            '%s is invalid: the base class cannot be created',
            self::describeDiscriminator($class, $tag),
        ));
    }

    /**
     * @param class-string $class
     * @param int|string|null $tag tag of the map, {@see null} for the type
     *        of the data with no known tag
     * @param class-string $child
     */
    public static function becauseDiscriminatorClassIsNotChild(
        string $class,
        int|string|null $tag,
        string $child,
    ): self {
        return new self(\sprintf(
            '%s is invalid: "%s" does not extend it',
            self::describeDiscriminator($class, $tag),
            \addcslashes($child, '"'),
        ));
    }

    /**
     * @param class-string $class
     * @param class-string $base class with the discriminator
     */
    public static function becauseClassIsNotInDiscriminatorMap(string $class, string $base): self
    {
        return new self(\sprintf(
            'Class "%s" is not in the discriminator map of "%s": neither it nor its children have a tag',
            \addcslashes($class, '"'),
            \addcslashes($base, '"'),
        ));
    }

    /**
     * @param class-string $class
     * @param non-empty-string $field
     * @param class-string $first base of the first tag
     * @param class-string $second base of the second tag
     */
    public static function becauseDiscriminatorFieldIsAmbiguous(
        string $class,
        string $field,
        string $first,
        int|string $firstTag,
        string $second,
        int|string $secondTag,
    ): self {
        return new self(\sprintf(
            'Class "%s" has two tags in field "%s": "%s" of class "%s" and "%s" of class "%s"',
            \addcslashes($class, '"'),
            \addcslashes($field, '"'),
            \addcslashes((string) $firstTag, '"'),
            \addcslashes($first, '"'),
            \addcslashes((string) $secondTag, '"'),
            \addcslashes($second, '"'),
        ));
    }

    /**
     * @param class-string $class
     */
    public static function becauseClassIsNotAccessible(string $class, \Throwable $previous): self
    {
        return new self(
            message: \sprintf(
                'Class "%s" cannot be mapped: %s',
                \addcslashes($class, '"'),
                $previous->getMessage(),
            ),
            previous: $previous,
        );
    }

    /**
     * @param class-string $class
     */
    private static function describeDiscriminator(string $class, int|string|null $tag): string
    {
        if ($tag === null) {
            return \sprintf('Type of "otherwise" of class "%s"', \addcslashes($class, '"'));
        }

        return \sprintf(
            'Type of tag "%s" of class "%s"',
            \addcslashes((string) $tag, '"'),
            \addcslashes($class, '"'),
        );
    }
}
