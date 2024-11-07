Feature: Checking the "mixed" (TypeLang\Mapper\Type\MixedType) type behavior

    Background:
        Given type "TypeLang\Mapper\Type\MixedType"

    Scenario Outline: Matching "<value>"
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            | 1                                                     | true       |
            | 0                                                     | true       |
            | -1                                                    | true       |
            | 42                                                    | true       |
            | 42.1                                                  | true       |
            | 1.0                                                   | true       |
            | 0.0                                                   | true       |
            | -1.0                                                  | true       |
            | INF                                                   | true       |
            | -INF                                                  | true       |
            | NAN                                                   | true       |
            | "1"                                                   | true       |
            | "0"                                                   | true       |
            | "string"                                              | true       |
            | "true"                                                | true       |
            | "false"                                               | true       |
            | ""                                                    | true       |
            | null                                                  | true       |
            | true                                                  | true       |
            | false                                                 | true       |
            | []                                                    | true       |
            | [1]                                                   | true       |
            | (object)[]                                            | true       |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | true       |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | true       |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | true       |

    Scenario Outline: Normalize "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result     |
            | 1                                                     | 1          |
            | 0                                                     | 0          |
            | -1                                                    | -1         |
            | 42                                                    | 42         |
            | 42.1                                                  | 42.1       |
            | 1.0                                                   | 1.0        |
            | 0.0                                                   | 0.0        |
            | -1.0                                                  | -1.0       |
            | INF                                                   | INF        |
            | -INF                                                  | -INF       |
            | NAN                                                   | NAN        |
            | "1"                                                   | "1"        |
            | "0"                                                   | "0"        |
            | "string"                                              | "string"   |
            | "true"                                                | "true"     |
            | "false"                                               | "false"    |
            | ""                                                    | ""         |
            | null                                                  | null       |
            | true                                                  | true       |
            | false                                                 | false      |
            | []                                                    | []         |
            | [1]                                                   | [1]        |
            | (object)[]                                            | []         |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | 3735928559 |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | "case"     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | "CASE"     |


    Scenario Outline: Denormalize "<value>"
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                                 |
            | 1                                                     | 1                                                                      |
            | 0                                                     | 0                                                                      |
            | -1                                                    | -1                                                                     |
            | 42                                                    | 42                                                                     |
            | 42.1                                                  | 42.1                                                                   |
            | 1.0                                                   | 1.0                                                                    |
            | 0.0                                                   | 0.0                                                                    |
            | -1.0                                                  | -1.0                                                                   |
            | INF                                                   | INF                                                                    |
            | -INF                                                  | -INF                                                                   |
            | NAN                                                   | NAN                                                                    |
            | "1"                                                   | "1"                                                                    |
            | "0"                                                   | "0"                                                                    |
            | "string"                                              | "string"                                                               |
            | "true"                                                | "true"                                                                 |
            | "false"                                               | "false"                                                                |
            | ""                                                    | ""                                                                     |
            | null                                                  | null                                                                   |
            | true                                                  | true                                                                   |
            | false                                                 | false                                                                  |
            | []                                                    | []                                                                     |
            | [1]                                                   | [1]                                                                    |
            | (object)[]                                            | (object)[]                                                             |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |
