Feature: Checking the "int" (TypeLang\Mapper\Platform\Standard\Type\IntType) type behavior

    Background:
        Given type "TypeLang\Mapper\Platform\Standard\Type\IntType"

    Scenario Outline: Matching "<value>"
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            # default checks
            ## int
            | 42                                                    | true       |
            | 1                                                     | true       |
            | 0                                                     | true       |
            | -1                                                    | true       |
            | -42                                                   | true       |
            ## numeric int string
            | "42"                                                  | false      |
            | "1"                                                   | false      |
            | "0"                                                   | false      |
            | "-1"                                                  | false      |
            | "-42"                                                 | false      |
            ## float
            | 42.5                                                  | false      |
            | 42.0                                                  | false      |
            | 1.0                                                   | false      |
            | 0.0                                                   | false      |
            | -1.0                                                  | false      |
            | -42.0                                                 | false      |
            | -42.5                                                 | false      |
            ## numeric float string
            | "42.5"                                                | false      |
            | "42.0"                                                | false      |
            | "1.0"                                                 | false      |
            | "0.0"                                                 | false      |
            | "-1.0"                                                | false      |
            | "-42.0"                                               | false      |
            | "-42.5"                                               | false      |
            ## extra float
            | INF                                                   | false      |
            | -INF                                                  | false      |
            | NAN                                                   | false      |
            ## null
            | null                                                  | false      |
            ## bool
            | true                                                  | false      |
            | false                                                 | false      |
            ## bool string
            | "true"                                                | false      |
            | "false"                                               | false      |
            ## string
            | "non empty"                                           | false      |
            | ""                                                    | false      |
            ## array
            | []                                                    | false      |
            | [0 => 23]                                             | false      |
            | ['key' => 42]                                         | false      |
            ## object
            | (object)[]                                            | false      |
            ## enum
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | false      |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | false      |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | false      |
            ### OVERFLOW EXTRAS ###
            | 9223372036854775807                                   | true       |
            | -9223372036854775807                                  | true       |
            | -9223372036854775807-1                                | true       |
            | -9223372036854775808                                  | false      |
            | -9223372036854775809                                  | false      |

    Scenario Outline: Normalization "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                             |
            # default checks
            ## int
            | 42                                                    | 42                                                                 |
            | 1                                                     | 1                                                                  |
            | 0                                                     | 0                                                                  |
            | -1                                                    | -1                                                                 |
            | -42                                                   | -42                                                                |
            ## numeric int string
            | "42"                                                  | 42                                                                 |
            | "1"                                                   | 1                                                                  |
            | "0"                                                   | 0                                                                  |
            | "-1"                                                  | -1                                                                 |
            | "-42"                                                 | -42                                                                |
            ## float
            | 42.5                                                  | <error: Passed value 42.5 is invalid>                              |
            | 42.0                                                  | 42                                                                 |
            | 1.0                                                   | 1                                                                  |
            | 0.0                                                   | 0                                                                  |
            | -1.0                                                  | -1                                                                 |
            | -42.0                                                 | -42                                                                |
            | -42.5                                                 | <error: Passed value -42.5 is invalid>                             |
            ## numeric float string
            | "42.5"                                                | <error: Passed value "42.5" is invalid>                            |
            | "42.0"                                                | 42                                                                 |
            | "1.0"                                                 | 1                                                                  |
            | "0.0"                                                 | 0                                                                  |
            | "-1.0"                                                | -1                                                                 |
            | "-42.0"                                               | -42                                                                |
            | "-42.5"                                               | <error: Passed value "-42.5" is invalid>                           |
            ## extra float
            | INF                                                   | <error: Passed value Infinity is invalid>                          |
            | -INF                                                  | <error: Passed value -Infinity is invalid>                         |
            | NAN                                                   | <error: Passed value NaN is invalid>                               |
            ## null
            | null                                                  | 0                                                                  |
            ## bool
            | true                                                  | 1                                                                  |
            | false                                                 | 0                                                                  |
            ## bool string
            | "true"                                                | <error: Passed value "true" is invalid>                            |
            | "false"                                               | <error: Passed value "false" is invalid>                           |
            ## string
            | "non empty"                                           | <error: Passed value "non empty" is invalid>                       |
            | ""                                                    | <error: Passed value "" is invalid>                                |
            ## array
            | []                                                    | <error: Passed value [] is invalid>                                |
            | [0 => 23]                                             | <error: Passed value [23] is invalid>                              |
            | ['key' => 42]                                         | <error: Passed value {"key": 42} is invalid>                       |
            ## object
            | (object)[]                                            | <error: Passed value {} is invalid>                                |
            ## enum
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | 3735928559                                                         |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid> |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                  |
            ### OVERFLOW EXTRAS ###
            | 9223372036854775807                                   | 9223372036854775807                                                |
            | -9223372036854775807                                  | -9223372036854775807                                               |
            | -9223372036854775807-1                                | -9223372036854775807-1                                             |
            | -9223372036854775808                                  | -9223372036854775807-1                                             |
            | -9223372036854775809                                  | -9223372036854775807-1                                             |

    Scenario Outline: Denormalize "<value>"
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                                 |
            # default checks
            ## int
            | 42                                                    | 42                                                                     |
            | 1                                                     | 1                                                                      |
            | 0                                                     | 0                                                                      |
            | -1                                                    | -1                                                                     |
            | -42                                                   | -42                                                                    |
            ## numeric int string
            | "42"                                                  | <error: Passed value "42" is invalid>                                  |
            | "1"                                                   | <error: Passed value "1" is invalid>                                   |
            | "0"                                                   | <error: Passed value "0" is invalid>                                   |
            | "-1"                                                  | <error: Passed value "-1" is invalid>                                  |
            | "-42"                                                 | <error: Passed value "-42" is invalid>                                 |
            ## float
            | 42.5                                                  | <error: Passed value 42.5 is invalid>                                  |
            | 42.0                                                  | <error: Passed value 42.0 is invalid>                                  |
            | 1.0                                                   | <error: Passed value 1.0 is invalid>                                   |
            | 0.0                                                   | <error: Passed value 0.0 is invalid>                                   |
            | -1.0                                                  | <error: Passed value -1.0 is invalid>                                  |
            | -42.0                                                 | <error: Passed value -42.0 is invalid>                                 |
            | -42.5                                                 | <error: Passed value -42.5 is invalid>                                 |
            ## numeric float string
            | "42.5"                                                | <error: Passed value "42.5" is invalid>                                |
            | "42.0"                                                | <error: Passed value "42.0" is invalid>                                |
            | "1.0"                                                 | <error: Passed value "1.0" is invalid>                                 |
            | "0.0"                                                 | <error: Passed value "0.0" is invalid>                                 |
            | "-1.0"                                                | <error: Passed value "-1.0" is invalid>                                |
            | "-42.0"                                               | <error: Passed value "-42.0" is invalid>                               |
            | "-42.5"                                               | <error: Passed value "-42.5" is invalid>                               |
            ## extra float
            | INF                                                   | <error: Passed value Infinity is invalid>                              |
            | -INF                                                  | <error: Passed value -Infinity is invalid>                             |
            | NAN                                                   | <error: Passed value NaN is invalid>                                   |
            ## null
            | null                                                  | <error: Passed value null is invalid>                                  |
            ## bool
            | true                                                  | <error: Passed value true is invalid>                                  |
            | false                                                 | <error: Passed value false is invalid>                                 |
            ## bool string
            | "true"                                                | <error: Passed value "true" is invalid>                                |
            | "false"                                               | <error: Passed value "false" is invalid>                               |
            ## string
            | "non empty"                                           | <error: Passed value "non empty" is invalid>                           |
            | ""                                                    | <error: Passed value "" is invalid>                                    |
            ## array
            | []                                                    | <error: Passed value [] is invalid>                                    |
            | [0 => 23]                                             | <error: Passed value [23] is invalid>                                  |
            | ['key' => 42]                                         | <error: Passed value {"key": 42} is invalid>                           |
            ## object
            | (object)[]                                            | <error: Passed value {} is invalid>                                    |
            ## enum
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |
            ### OVERFLOW EXTRAS ###
            | 9223372036854775807                                   | 9223372036854775807                                                    |
            | -9223372036854775807                                  | -9223372036854775807                                                   |
            | -9223372036854775807-1                                | -9223372036854775807-1                                                 |
            | -9223372036854775808                                  | <error: Passed value -9223372036854775808.0 is invalid>                  |
            | -9223372036854775809                                  | <error: Passed value -9223372036854775808.0 is invalid>                  |
