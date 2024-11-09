Feature: Checking the "array-key" (TypeLang\Mapper\Type\ArrayKeyType) type behavior

    Background:
        Given type "TypeLang\Mapper\Type\ArrayKeyType"

    Scenario Outline: Matching "<value>"
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            | 9223372036854775807                                   | true       |
            | -9223372036854775807                                  | true       |
            | -9223372036854775807-1                                | true       |
            | -9223372036854775808                                  | false      |
            | -9223372036854775809                                  | false      |
            | 1                                                     | true       |
            | 0                                                     | true       |
            | -1                                                    | true       |
            | 42                                                    | true       |
            | 42.1                                                  | false      |
            | 1.0                                                   | false      |
            | 0.0                                                   | false      |
            | -1.0                                                  | false      |
            | INF                                                   | false      |
            | -INF                                                  | false      |
            | NAN                                                   | false      |
            | "1"                                                   | true       |
            | "0"                                                   | true       |
            | "string"                                              | true       |
            | "true"                                                | true       |
            | "false"                                               | true       |
            | ""                                                    | true       |
            | null                                                  | false      |
            | true                                                  | false      |
            | false                                                 | false      |
            | []                                                    | false      |
            | [1]                                                   | false      |
            | (object)[]                                            | false      |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | false      |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | false      |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | false      |


    Scenario Outline: Normalize "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                            |
            | 9223372036854775807                                   | 9223372036854775807                               |
            | -9223372036854775807-1                                | -9223372036854775807-1                            |
            | -9223372036854775808                                  | -9223372036854775807-1                            |
            | 1                                                     | 1                                                 |
            | 0                                                     | 0                                                 |
            | -1                                                    | -1                                                |
            | 42                                                    | 42                                                |
            | 42.1                                                  | "42.1"                                            |
            | 1.0                                                   | 1                                                 |
            | 0.0                                                   | 0                                                 |
            | -1.0                                                  | -1                                                |
            | INF                                                   | "inf"                                             |
            | -INF                                                  | "-inf"                                            |
            | NAN                                                   | "nan"                                             |
            | "1"                                                   | "1"                                               |
            | "0"                                                   | 0                                                 |
            | "string"                                              | "string"                                          |
            | "true"                                                | "true"                                            |
            | "false"                                               | "false"                                           |
            | ""                                                    | ""                                                |
            | null                                                  | ""                                                |
            | true                                                  | "true"                                            |
            | false                                                 | "false"                                           |
            | []                                                    | <error: Passed value [] is invalid>               |
            | [1]                                                   | <error: Passed value [1] is invalid>              |
            | (object)[]                                            | <error: Passed value {} is invalid>               |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | "3735928559"                                      |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | "case"                                            |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid> |


    Scenario Outline: Denormalize "<value>"
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                                 |
            | 9223372036854775807                                   | 9223372036854775807                                                    |
            | -9223372036854775807                                  | -9223372036854775807                                                   |
            | -9223372036854775807-1                                | -9223372036854775807-1                                                 |
            | -9223372036854775808                                  | <error: Passed value -9.2233720368548E+18 is invalid>                  |
            | -9223372036854775809                                  | <error: Passed value -9.2233720368548E+18 is invalid>                  |
            | 1                                                     | 1                                                                      |
            | 0                                                     | 0                                                                      |
            | -1                                                    | -1                                                                     |
            | 42                                                    | 42                                                                     |
            | 42.1                                                  | <error: Passed value 42.1 is invalid>                                  |
            | 1.0                                                   | <error: Passed value 1 is invalid>                                     |
            | 0.0                                                   | <error: Passed value 0 is invalid>                                     |
            | -1.0                                                  | <error: Passed value -1 is invalid>                                    |
            | INF                                                   | <error: Passed value INF is invalid>                                   |
            | -INF                                                  | <error: Passed value -INF is invalid>                                  |
            | NAN                                                   | <error: Passed value NAN is invalid>                                   |
            | "1"                                                   | "1"                                                                    |
            | "0"                                                   | 0                                                                      |
            | "-1"                                                  | "-1"                                                                   |
            | "string"                                              | "string"                                                               |
            | "true"                                                | "true"                                                                 |
            | "false"                                               | "false"                                                                |
            | ""                                                    | ""                                                                     |
            | null                                                  | <error: Passed value null is invalid>                                  |
            | true                                                  | <error: Passed value true is invalid>                                  |
            | false                                                 | <error: Passed value false is invalid>                                 |
            | []                                                    | <error: Passed value [] is invalid>                                    |
            | [1]                                                   | <error: Passed value [1] is invalid>                                   |
            | (object)[]                                            | <error: Passed value {} is invalid>                                    |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |
