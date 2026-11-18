<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\TestCase;

final class DateTimeTypeTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{
     *     non-empty-string,
     *     mixed,
     *     \DateTimeInterface,
     * }>
     */
    public static function denormalizedDataProvider(): iterable
    {
        $date = new \DateTimeImmutable('2021-02-03 04:05:06 UTC');

        yield 'interface' => ['DateTimeInterface', '2021-02-03T04:05:06+00:00', $date];
        yield 'immutable class' => ['DateTimeImmutable', '2021-02-03 04:05:06 UTC', $date];
        yield 'mutable class' => ['DateTime', '2021-02-03 04:05:06 UTC', new \DateTime('2021-02-03 04:05:06 UTC')];
        yield 'format without time' => [
            "DateTimeImmutable<'Y-m-d'>",
            '2021-02-03',
            new \DateTimeImmutable('2021-02-03 00:00:00'),
        ];
        yield 'object of the type' => ['DateTimeInterface', $date, $date];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('denormalizedDataProvider')]
    public function testDenormalizesDate(string $type, mixed $input, \DateTimeInterface $expected): void
    {
        self::assertDenormalizes($type, $input, $expected);
    }

    /**
     * @return iterable<non-empty-string, array{non-empty-string, mixed}>
     */
    public static function rejectedDataProvider(): iterable
    {
        yield 'not a date' => ['DateTimeImmutable', 'not a date'];
        yield 'not of the format' => ["DateTimeImmutable<'Y-m-d'>", '03.02.2021'];
        yield 'impossible date' => ["DateTimeImmutable<'Y-m-d'>", '2021-02-30'];
        yield 'trailing data' => ["DateTimeImmutable<'Y-m-d'>", '2021-02-03 04:05'];
        yield 'empty string' => ['DateTimeImmutable', ''];
    }

    /**
     * @param non-empty-string $type
     */
    #[DataProvider('rejectedDataProvider')]
    public function testRejectsStringThatIsNotDate(string $type, mixed $input): void
    {
        self::assertDenormalizationFails(
            $type,
            $input,
            \sprintf('$: Value must be of type string, but "%s" given', $input),
        );
    }

    public function testRejectsStringWithNulByte(): void
    {
        foreach (['DateTimeImmutable', "DateTimeImmutable<'Y-m-d'>"] as $type) {
            self::assertDenormalizationFails(
                $type,
                "2021-02-03\0",
                '$: Value must be of type string, but "2021-02-03\000" given',
            );
        }
    }

    public function testNormalizesDateByFormat(): void
    {
        $date = new \DateTime('2021-02-03 04:05:06 UTC');

        self::assertNormalizes('DateTimeInterface', $date, '2021-02-03T04:05:06+00:00');
        self::assertNormalizes("DateTimeInterface<'Y-m-d'>", $date, '2021-02-03');
        self::assertNormalizes('mixed', $date, '2021-02-03T04:05:06+00:00');
    }

    public function testNormalizationRejectsDateOfOtherClass(): void
    {
        self::assertNormalizationFails(
            'DateTimeImmutable',
            new \DateTime('2021-02-03'),
            '$: Value must be of type DateTimeImmutable, but object(DateTime) given',
        );
    }

    public function testInterfaceIsCreatedAsImmutable(): void
    {
        $date = self::createMapper()->denormalize('2020-01-02T03:04:05+00:00', 'DateTimeInterface');

        self::assertInstanceOf(\DateTimeImmutable::class, $date);
    }

    public function testFormatMustBeSingleNonEmptyString(): void
    {
        foreach (["DateTime<'Y', 'm'>", 'DateTime<int>', "DateTime<''>"] as $type) {
            $error = self::captureBuildError($type);

            self::assertInstanceOf(TypeDefinitionException::class, $error);
            self::assertSame(
                \sprintf('Template argument #1 of type "%s" is invalid: ', $type)
                    . 'must be the only one, a non-empty string of the format',
                $error->getMessage(),
            );
        }
    }
}
