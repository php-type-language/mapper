<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeCoercerInterface<string>
 */
class StringTypeCoercer implements TypeCoercerInterface
{
    /**
     * @var int<1, 53>
     */
    private const DEFAULT_FLOAT_PRECISION = 14;

    /** @var string */
    public const NULL_TO_STRING = '';
    /** @var string */
    public const TRUE_TO_STRING = 'true';
    /** @var string */
    public const FALSE_TO_STRING = 'false';
    /** @var string */
    public const NAN_TO_STRING = 'nan';
    /** @var string */
    public const INF_TO_STRING = 'inf';

    /**
     * @var non-empty-string
     */
    private string $floatTemplate;

    /**
     * @param int<1, 53>|null $floatPrecision
     */
    public function __construct(?int $floatPrecision = null)
    {
        $this->floatTemplate = $this->createFloatTemplate(
            precision: $floatPrecision ?? $this->getDefaultFloatPrecision(),
        );
    }

    /**
     * @param int<1, 53> $precision
     *
     * @return non-empty-string
     */
    private function createFloatTemplate(int $precision): string
    {
        return \sprintf('%%01.%df', $precision);
    }

    /**
     * @return int<1, 53>
     */
    private function getDefaultFloatPrecision(): int
    {
        $result = \ini_get('precision');

        if (IntTypeCoercer::isSafeFloat((float) $result)) {
            return \max(1, \min(53, (int) $result));
        }

        return self::DEFAULT_FLOAT_PRECISION;
    }

    /**
     * @throws InvalidValueException
     */
    public function coerce(mixed $value, RuntimeContext $context): string
    {
        return match (true) {
            // string
            \is_string($value) => $value,
            // Null
            $value === null => static::NULL_TO_STRING,
            // True
            $value === true => static::TRUE_TO_STRING,
            // False
            $value === false => static::FALSE_TO_STRING,
            // Float
            \is_float($value) => match (true) {
                // NaN
                \is_nan($value) => static::NAN_TO_STRING,
                // Infinity
                $value === \INF => static::INF_TO_STRING,
                $value === -\INF => '-' . static::INF_TO_STRING,
                // Other floating point values
                default => $this->floatToString($value, $context),
            },
            // Int
            \is_int($value),
            // Stringable
            $value instanceof \Stringable => (string) $value,
            // Enum
            $value instanceof \BackedEnum => (string) $value->value,
            $value instanceof \UnitEnum => $value->name,
            // Resource
            \is_resource($value) => \get_resource_type($value),
            \get_debug_type($value) === 'resource (closed)' => 'resource',
            default => throw InvalidValueException::createFromContext($context),
        };
    }

    private function floatToString(float $value, RuntimeContext $context): string
    {
        $formatted = \sprintf($this->floatTemplate, $value);
        $formatted = \rtrim($formatted, '0');

        if (\str_ends_with($formatted, '.')) {
            $formatted .= '0';
        }

        if ((float) $formatted === $value) {
            return $formatted;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
