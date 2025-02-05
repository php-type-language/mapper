Feature: Checking the "bool" (TypeLang\Mapper\Platform\Standard\Type\BoolType) type behavior

    Background:
        Given type "TypeLang\Mapper\Platform\Standard\Type\BoolType"

    Scenario Outline: Matching "<value>"
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            # default checks
            ## int
            | 42                                                    | false      |
            | 1                                                     | false      |
            | 0                                                     | false      |
            | -1                                                    | false      |
            | -42                                                   | false      |
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
            | true                                                  | true       |
            | false                                                 | true       |
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

    Scenario Outline: Normalize "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result |
            # default checks
            ## int
            | 42                                                    | true   |
            | 1                                                     | true   |
            | 0                                                     | false  |
            | -1                                                    | true   |
            | -42                                                   | true   |
            ## numeric int string
            | "42"                                                  | true   |
            | "1"                                                   | true   |
            | "0"                                                   | false  |
            | "-1"                                                  | true   |
            | "-42"                                                 | true   |
            ## float
            | 42.5                                                  | true   |
            | 42.0                                                  | true   |
            | 1.0                                                   | true   |
            | 0.0                                                   | false  |
            | -1.0                                                  | true   |
            | -42.0                                                 | true   |
            | -42.5                                                 | true   |
            ## numeric float string
            | "42.5"                                                | true   |
            | "42.0"                                                | true   |
            | "1.0"                                                 | true   |
            | "0.0"                                                 | true   |
            | "-1.0"                                                | true   |
            | "-42.0"                                               | true   |
            | "-42.5"                                               | true   |
            ## extra float
            | INF                                                   | true   |
            | -INF                                                  | true   |
            | NAN                                                   | true   |
            ## null
            | null                                                  | false  |
            ## bool
            | true                                                  | true   |
            | false                                                 | FALSE  |
            ## bool string
            | "true"                                                | true   |
            | "false"                                               | true   |
            ## string
            | "non empty"                                           | true   |
            | ""                                                    | false  |
            ## array
            | []                                                    | false  |
            | [0 => 23]                                             | true   |
            | ['key' => 42]                                         | true   |
            ## object
            | (object)[]                                            | true   |
            ## enum
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | true   |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | true   |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | true   |

    Scenario Outline: Denormalize "<value>"
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                                 |
            # default checks
            ## int
            | 42                                                    | <error: Passed value 42 is invalid>                                    |
            | 1                                                     | <error: Passed value 1 is invalid>                                     |
            | 0                                                     | <error: Passed value 0 is invalid>                                     |
            | -1                                                    | <error: Passed value -1 is invalid>                                    |
            | -42                                                   | <error: Passed value -42 is invalid>                                   |
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
            | true                                                  | true                                                                   |
            | false                                                 | false                                                                  |
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
