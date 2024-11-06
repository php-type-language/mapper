Feature: Checking the "StringType" type behavior

    Background:
        Given type "TypeLang\Mapper\Type\StringType"

    Scenario Outline: Matching "<value>" by the StringType
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            | 42                                                    | false      |
            | 42.1                                                  | false      |
            | INF                                                   | false      |
            | -INF                                                  | false      |
            | NAN                                                   | false      |
            | "string"                                              | true       |
            | null                                                  | false      |
            | true                                                  | false      |
            | false                                                 | false      |
            | []                                                    | false      |
            | (object)[]                                            | false      |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | false      |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | false      |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | false      |

    Scenario Outline: Normalize "<value>" by the StringType
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                            |
            | 42                                                    | "42"                                              |
            | -9223372036854775808                                  | "-9.2233720368548E+18"                            |
            | 42.1                                                  | "42.1"                                            |
            | INF                                                   | "inf"                                             |
            | -INF                                                  | "-inf"                                            |
            | NAN                                                   | "nan"                                             |
            | "string"                                              | "string"                                          |
            | null                                                  | ""                                                |
            | true                                                  | "true"                                            |
            | false                                                 | "false"                                           |
            | []                                                    | <error: Passed value [] is invalid>               |
            | (object)[]                                            | <error: Passed value {} is invalid>               |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | "3735928559"                                      |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | "case"                                            |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid> |

    Scenario Outline: Denormalize "<value>" by the StringType
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                                 |
            | 42                                                    | <error: Passed value 42 is invalid>                                    |
            | -9223372036854775808                                  | <error: Passed value -9.2233720368548E+18 is invalid>                  |
            | 42.1                                                  | <error: Passed value 42.1 is invalid>                                  |
            | INF                                                   | <error: Passed value INF is invalid>                                   |
            | -INF                                                  | <error: Passed value -INF is invalid>                                  |
            | NAN                                                   | <error: Passed value NAN is invalid>                                   |
            | "string"                                              | "string"                                                               |
            | null                                                  | <error: Passed value null is invalid>                                  |
            | true                                                  | <error: Passed value true is invalid>                                  |
            | false                                                 | <error: Passed value false is invalid>                                 |
            | []                                                    | <error: Passed value [] is invalid>                                    |
            | (object)[]                                            | <error: Passed value {} is invalid>                                    |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |
