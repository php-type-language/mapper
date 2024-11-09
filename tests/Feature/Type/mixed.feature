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
            # default checks
            ## int
            | 42                                                    | true       |
            | 1                                                     | true       |
            | 0                                                     | true       |
            | -1                                                    | true       |
            | -42                                                   | true       |
            ## numeric int string
            | "42"                                                  | true       |
            | "1"                                                   | true       |
            | "0"                                                   | true       |
            | "-1"                                                  | true       |
            | "-42"                                                 | true       |
            ## float
            | 42.5                                                  | true       |
            | 42.0                                                  | true       |
            | 1.0                                                   | true       |
            | 0.0                                                   | true       |
            | -1.0                                                  | true       |
            | -42.0                                                 | true       |
            | -42.5                                                 | true       |
            ## numeric float string
            | "42.5"                                                | true       |
            | "42.0"                                                | true       |
            | "1.0"                                                 | true       |
            | "0.0"                                                 | true       |
            | "-1.0"                                                | true       |
            | "-42.0"                                               | true       |
            | "-42.5"                                               | true       |
            ## extra float
            | INF                                                   | true       |
            | -INF                                                  | true       |
            | NAN                                                   | true       |
            ## null
            | null                                                  | true       |
            ## bool
            | true                                                  | true       |
            | false                                                 | true       |
            ## bool string
            | "true"                                                | true       |
            | "false"                                               | true       |
            ## string
            | "non empty"                                           | true       |
            | ""                                                    | true       |
            ## array
            | []                                                    | true       |
            | [0 => 23]                                             | true       |
            | ['key' => 42]                                         | true       |
            ## object
            | (object)[]                                            | true       |
            ## enum
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | true       |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | true       |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | true       |

    Scenario Outline: Normalize "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result        |
            # default checks
            ## int
            | 42                                                    | 42            |
            | 1                                                     | 1             |
            | 0                                                     | 0             |
            | -1                                                    | -1            |
            | -42                                                   | -42           |
            ## numeric int string
            | "42"                                                  | "42"          |
            | "1"                                                   | "1"           |
            | "0"                                                   | "0"           |
            | "-1"                                                  | "-1"          |
            | "-42"                                                 | "-42"         |
            ## float
            | 42.5                                                  | 42.5          |
            | 42.0                                                  | 42.0          |
            | 1.0                                                   | 1.0           |
            | 0.0                                                   | 0.0           |
            | -1.0                                                  | -1.0          |
            | -42.0                                                 | -42.0         |
            | -42.5                                                 | -42.5         |
            ## numeric float string
            | "42.5"                                                | "42.5"        |
            | "42.0"                                                | "42.0"        |
            | "1.0"                                                 | "1.0"         |
            | "0.0"                                                 | "0.0"         |
            | "-1.0"                                                | "-1.0"        |
            | "-42.0"                                               | "-42.0"       |
            | "-42.5"                                               | "-42.5"       |
            ## extra float
            | INF                                                   | INF           |
            | -INF                                                  | -INF          |
            | NAN                                                   | NAN           |
            ## null
            | null                                                  | null          |
            ## bool
            | true                                                  | true          |
            | false                                                 | false         |
            ## bool string
            | "true"                                                | "true"        |
            | "false"                                               | "false"       |
            ## string
            | "non empty"                                           | "non empty"   |
            | ""                                                    | ""            |
            ## array
            | []                                                    | []            |
            | [0 => 23]                                             | [0 => 23]     |
            | ['key' => 42]                                         | ['key' => 42] |
            ## object
            | (object)[]                                            | []            |
            ## enum
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | 3735928559    |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | "case"        |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | "CASE"        |


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
            | "42"                                                  | "42"                                                                   |
            | "1"                                                   | "1"                                                                    |
            | "0"                                                   | "0"                                                                    |
            | "-1"                                                  | "-1"                                                                   |
            | "-42"                                                 | "-42"                                                                  |
            ## float
            | 42.5                                                  | 42.5                                                                   |
            | 42.0                                                  | 42.0                                                                   |
            | 1.0                                                   | 1.0                                                                    |
            | 0.0                                                   | 0.0                                                                    |
            | -1.0                                                  | -1.0                                                                   |
            | -42.0                                                 | -42.0                                                                  |
            | -42.5                                                 | -42.5                                                                  |
            ## numeric float string
            | "42.5"                                                | "42.5"                                                                 |
            | "42.0"                                                | "42.0"                                                                 |
            | "1.0"                                                 | "1.0"                                                                  |
            | "0.0"                                                 | "0.0"                                                                  |
            | "-1.0"                                                | "-1.0"                                                                 |
            | "-42.0"                                               | "-42.0"                                                                |
            | "-42.5"                                               | "-42.5"                                                                |
            ## extra float
            | INF                                                   | INF                                                                    |
            | -INF                                                  | -INF                                                                   |
            | NAN                                                   | NAN                                                                    |
            ## null
            | null                                                  | null                                                                   |
            ## bool
            | true                                                  | true                                                                   |
            | false                                                 | false                                                                  |
            ## bool string
            | "true"                                                | "true"                                                                 |
            | "false"                                               | "false"                                                                |
            ## string
            | "non empty"                                           | "non empty"                                                            |
            | ""                                                    | ""                                                                     |
            ## array
            | []                                                    | []                                                                     |
            | [0 => 23]                                             | [0 => 23]                                                              |
            | ['key' => 42]                                         | ['key' => 42]                                                          |
            ## object
            | (object)[]                                            | (object)[]                                                             |
            ## enum
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |
