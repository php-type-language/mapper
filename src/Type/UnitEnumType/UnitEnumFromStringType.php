<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\UnitEnumType;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TEnum of \UnitEnum = \UnitEnum
 *
 * @template-implements TypeInterface<TEnum, string>
 */
class UnitEnumFromStringType implements TypeInterface
{
    /**
     * @var list<non-empty-string>
     */
    private readonly array $cases;

    public function __construct(
        /**
         * @var class-string<TEnum>
         */
        protected readonly string $class,
        /**
         * @var TypeInterface<string, string>
         */
        protected readonly TypeInterface $type,
    ) {
        $this->cases = $this->getEnumCases($class);
    }

    /**
     * @param class-string<TEnum> $enum
     *
     * @return list<non-empty-string>
     */
    private function getEnumCases(string $enum): array
    {
        $result = [];

        foreach ($enum::cases() as $case) {
            if ($case->name === '') {
                continue;
            }

            $result[] = $case->name;
        }

        return $result;
    }

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        $result = $this->type->match($value, $context);

        return $result?->if(\in_array($result->value, $this->cases, true));
    }

    public function cast(mixed $value, RuntimeContext $context): \UnitEnum
    {
        $string = $this->type->cast($value, $context);

        if (!\in_array($string, $this->cases, true)) {
            throw InvalidValueException::createFromContext($context);
        }

        try {
            // @phpstan-ignore-next-line : Handle Error manually
            return \constant($this->class . '::' . $string);
        } catch (\Error $e) {
            throw InvalidValueException::createFromContext(
                context: $context,
                previous: $e,
            );
        }
    }
}
