<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming\Tests\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Naming\Internal\Name;
use TypeLang\Mapper\Naming\Internal\WordCase;
use TypeLang\Mapper\Naming\Tests\TestCase;

final class NameTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{string, list<non-empty-string>}>
     */
    public static function wordsDataProvider(): iterable
    {
        yield 'single lowercase word' => ['name', ['name']];
        yield 'camel case' => ['firstName', ['first', 'Name']];
        yield 'pascal case' => ['FirstName', ['First', 'Name']];
        yield 'trailing abbreviation' => ['userID', ['user', 'ID']];
        yield 'leading abbreviation' => ['HTTPServer', ['HTTP', 'Server']];
        yield 'inner abbreviation' => [
            'getHTTPResponseCode',
            ['get', 'HTTP', 'Response', 'Code'],
        ];
        yield 'abbreviation and capitalized word' => [
            'XMLHttpRequest',
            ['XML', 'Http', 'Request'],
        ];
        yield 'two letter abbreviation' => ['IOStream', ['IO', 'Stream']];
        yield 'abbreviation only' => ['ID', ['ID']];
        yield 'trailing digit' => ['address2', ['address2']];
        yield 'digit after separator' => ['address_2', ['address', '2']];
        yield 'inner digit' => ['utf8Encode', ['utf8', 'Encode']];
        yield 'digit after abbreviation' => ['HTTP2Server', ['HTTP2', 'Server']];
        yield 'lowercase letter after digit' => ['render3dModel', ['render3d', 'Model']];
        yield 'uppercase letter after digit' => ['x1Y2', ['x1', 'Y2']];
        yield 'snake case' => ['already_snake', ['already', 'snake']];
        yield 'kebab case' => ['kebab-case', ['kebab', 'case']];
        yield 'screaming snake case' => ['SCREAMING_SNAKE', ['SCREAMING', 'SNAKE']];
        yield 'hyphenated capitalized words' => ['Content-Type', ['Content', 'Type']];
        yield 'leading, repeated and trailing separators' => ['__private__name_', ['private', 'name']];
        yield 'single lowercase letter' => ['a', ['a']];
        yield 'single uppercase letter' => ['A', ['A']];
        yield 'single letter words' => ['aB', ['a', 'B']];
        yield 'plural abbreviation' => ['listURLs', ['list', 'URLs']];
        yield 'inner plural abbreviation' => ['getIDsAndNames', ['get', 'IDs', 'And', 'Names']];
        yield 'abbreviation before word starting with s' => ['HTTPServers', ['HTTP', 'Servers']];
        yield 'abbreviation before lowercase s word' => ['ABsolute', ['A', 'Bsolute']];
        yield 'non-ASCII letters' => ['größe', ['größe']];
        yield 'leading digit' => ['2fa', ['2fa']];
        yield 'space separator' => ['first name', ['first', 'name']];
        yield 'dot separator' => ['first.name', ['first', 'name']];
        yield 'empty string' => ['', []];
        yield 'underscore only' => ['_', []];
        yield 'separators only' => ['_-.', []];
    }

    /**
     * @param list<non-empty-string> $expected
     */
    #[DataProvider('wordsDataProvider')]
    public function testSplitsNameIntoWords(string $name, array $expected): void
    {
        self::assertSame($expected, Name::parse($name)->words);
    }

    /**
     * @return iterable<non-empty-string, array{string, string, string}>
     */
    public static function affixesDataProvider(): iterable
    {
        yield 'no separators' => ['userId', '', ''];
        yield 'leading underscore' => ['_id', '_', ''];
        yield 'leading and trailing separators' => ['__private__name_', '__', '_'];
        yield 'trailing hyphen' => ['name-', '', '-'];
        yield 'inner separators only' => ['first_name', '', ''];
        yield 'empty string' => ['', '', ''];
        yield 'separators only' => ['_-.', '', '_-.'];
    }

    #[DataProvider('affixesDataProvider')]
    public function testKeepsSeparatorsAroundWords(string $name, string $prefix, string $suffix): void
    {
        $parsed = Name::parse($name);

        self::assertSame($prefix, $parsed->prefix);
        self::assertSame($suffix, $parsed->suffix);
    }

    public function testJoinsWordsInCasesOfFirstAndOtherWords(): void
    {
        $name = Name::parse('__getHTTPCode_');

        self::assertSame('__get-Http-Code_', $name->join('-', WordCase::Lower, WordCase::Capitalized));
        self::assertSame('__GETHTTPCODE_', $name->join('', WordCase::Upper, WordCase::Upper));
    }

    public function testNameWithoutWordsIsJoinedAsIs(): void
    {
        self::assertSame('_-.', Name::parse('_-.')->join('_', WordCase::Upper, WordCase::Upper));
    }
}
