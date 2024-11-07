Feature: Checking the "int" (TypeLang\Mapper\Type\IntType) type behavior

    Background:
        Given type "TypeLang\Mapper\Type\IntType"

    Scenario Outline: Matching "<value>"
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            | 9223372036854775807                                   | true       |
            | -9223372036854775807-1                                | true       |
            | -9223372036854775808                                  | true       |
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
            | "1"                                                   | false      |
            | "1.0"                                                 | false      |
            | "0"                                                   | false      |
            | "0.0"                                                 | false      |
            | "-1"                                                  | false      |
            | "-1.0"                                                | false      |
            | "string"                                              | false      |
            | "true"                                                | false      |
            | "false"                                               | false      |
            | ""                                                    | false      |
            | null                                                  | false      |
            | true                                                  | false      |
            | false                                                 | false      |
            | []                                                    | false      |
            | [1]                                                   | false      |
            | (object)[]                                            | false      |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | false      |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | false      |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | false      |

    Scenario Outline: Normalization "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                             |
            | 9223372036854775807                                   | 9223372036854775807                                                |
            | -9223372036854775807-1                                | -9223372036854775807-1                                             |
            | -9223372036854775808                                  | -9223372036854775807-1                                             |
            | 1                                                     | 1                                                                  |
            | 0                                                     | 0                                                                  |
            | -1                                                    | -1                                                                 |
            | 42                                                    | 42                                                                 |
            | 42.1                                                  | <error: Passed value 42.1 is invalid>                              |
            # Conversion from float without loss of precision
            | 1.0                                                   | 1                                                                  |
            | 0.0                                                   | 0                                                                  |
            | -1.0                                                  | -1                                                                 |
            | INF                                                   | <error: Passed value INF is invalid>                               |
            | -INF                                                  | <error: Passed value -INF is invalid>                              |
            | NAN                                                   | <error: Passed value NAN is invalid>                               |
            # Conversion from stringable int without loss of precision
            | "1"                                                   | 1                                                                  |
            | "0"                                                   | 0                                                                  |
            | "-1"                                                  | -1                                                                 |
            # Conversion from stringable float without loss of precision
            | "1.0"                                                 | 1                                                                  |
            | "0.0"                                                 | 0                                                                  |
            | "-1.0"                                                | -1                                                                 |
            | "string"                                              | <error: Passed value "string" is invalid>                          |
            | "true"                                                | <error: Passed value "true" is invalid>                            |
            | "false"                                               | <error: Passed value "false" is invalid>                           |
            | ""                                                    | <error: Passed value "" is invalid>                                |
            | null                                                  | 0                                                                  |
            | true                                                  | 1                                                                  |
            | false                                                 | 0                                                                  |
            | []                                                    | <error: Passed value [] is invalid>                                |
            | [1]                                                   | <error: Passed value [1] is invalid>                               |
            | (object)[]                                            | <error: Passed value {} is invalid>                                |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | 3735928559                                                         |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid> |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                  |


    Scenario Outline: Denormalization "<value>"
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                                 |
            | 9223372036854775807                                   | 9223372036854775807                                                    |
            | -9223372036854775807-1                                | -9223372036854775807-1                                                 |
            | -9223372036854775808                                  | -9223372036854775807-1                                                 |
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
            | "1"                                                   | <error: Passed value "1" is invalid>                                   |
            | "1.0"                                                 | <error: Passed value "1.0" is invalid>                                 |
            | "0"                                                   | <error: Passed value "0" is invalid>                                   |
            | "0.0"                                                 | <error: Passed value "0.0" is invalid>                                 |
            | "-1"                                                  | <error: Passed value "-1" is invalid>                                  |
            | "-1.0"                                                | <error: Passed value "-1.0" is invalid>                                |
            | "string"                                              | <error: Passed value "string" is invalid>                              |
            | "true"                                                | <error: Passed value "true" is invalid>                                |
            | "false"                                               | <error: Passed value "false" is invalid>                               |
            | ""                                                    | <error: Passed value "" is invalid>                                    |
            | null                                                  | <error: Passed value null is invalid>                                  |
            | true                                                  | <error: Passed value true is invalid>                                  |
            | false                                                 | <error: Passed value false is invalid>                                 |
            | []                                                    | <error: Passed value [] is invalid>                                    |
            | [1]                                                   | <error: Passed value [1] is invalid>                                   |
            | (object)[]                                            | <error: Passed value {} is invalid>                                    |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |
