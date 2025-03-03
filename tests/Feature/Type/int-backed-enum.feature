Feature: Checking the "enum<int>" (TypeLang\Mapper\Platform\Standard\Type\BackedEnumType) type behavior

    Background:
        Given type "int-backed-enum"

    Scenario Outline: Normalize matching "<value>"
        When normalize
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
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | true       |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | false      |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | false      |
            ### ADDITIONAL SPECIAL CASES ###
            | "case"                                                | false      |
            | 3735928559                                            | false      |
            | -3735928559                                           | false      |

    Scenario Outline: Denormalize matching "<value>"
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
            ### ADDITIONAL SPECIAL CASES ###
            | "case"                                                | false      |
            | 3735928559                                            | true       |
            | -3735928559                                           | false      |

    Scenario Outline: Normalize "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                             |
            # default checks
            ## int
            | 42                                                    | <error: Passed value 42 is invalid>                                |
            | 1                                                     | <error: Passed value 1 is invalid>                                 |
            | 0                                                     | <error: Passed value 0 is invalid>                                 |
            | -1                                                    | <error: Passed value -1 is invalid>                                |
            | -42                                                   | <error: Passed value -42 is invalid>                               |
            ## numeric int string
            | "42"                                                  | <error: Passed value "42" is invalid>                              |
            | "1"                                                   | <error: Passed value "1" is invalid>                               |
            | "0"                                                   | <error: Passed value "0" is invalid>                               |
            | "-1"                                                  | <error: Passed value "-1" is invalid>                              |
            | "-42"                                                 | <error: Passed value "-42" is invalid>                             |
            ## float
            | 42.5                                                  | <error: Passed value 42.5 is invalid>                              |
            | 42.0                                                  | <error: Passed value 42.0 is invalid>                              |
            | 1.0                                                   | <error: Passed value 1.0 is invalid>                               |
            | 0.0                                                   | <error: Passed value 0.0 is invalid>                               |
            | -1.0                                                  | <error: Passed value -1.0 is invalid>                              |
            | -42.0                                                 | <error: Passed value -42.0 is invalid>                             |
            | -42.5                                                 | <error: Passed value -42.5 is invalid>                             |
            ## numeric float string
            | "42.5"                                                | <error: Passed value "42.5" is invalid>                            |
            | "42.0"                                                | <error: Passed value "42.0" is invalid>                            |
            | "1.0"                                                 | <error: Passed value "1.0" is invalid>                             |
            | "0.0"                                                 | <error: Passed value "0.0" is invalid>                             |
            | "-1.0"                                                | <error: Passed value "-1.0" is invalid>                            |
            | "-42.0"                                               | <error: Passed value "-42.0" is invalid>                           |
            | "-42.5"                                               | <error: Passed value "-42.5" is invalid>                           |
            ## extra float
            | INF                                                   | <error: Passed value Infinity is invalid>                          |
            | -INF                                                  | <error: Passed value -Infinity is invalid>                         |
            | NAN                                                   | <error: Passed value NaN is invalid>                               |
            ## null
            | null                                                  | <error: Passed value null is invalid>                              |
            ## bool
            | true                                                  | <error: Passed value true is invalid>                              |
            | false                                                 | <error: Passed value false is invalid>                             |
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
            ### ADDITIONAL SPECIAL CASES ###
            | "case"                                                | <error: Passed value "case" is invalid>                            |
            | 3735928559                                            | <error: Passed value 3735928559 is invalid>                        |
            | -3735928559                                           | <error: Passed value -3735928559 is invalid>                       |

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
            ### ADDITIONAL SPECIAL CASES ###
            | "case"                                                | <error: Passed value "case" is invalid>                                |
            | 3735928559                                            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE                     |
            | -3735928559                                           | <error: Passed value -3735928559 is invalid>                           |
