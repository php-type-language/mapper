Feature: Checking the "true" (TypeLang\Mapper\Type\BoolLiteralType) type behavior

    Background:
        Given type "TypeLang\Mapper\Type\BoolLiteralType" with [true]

    Scenario Outline: Matching "<value>"
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            | 1                                                     | false      |
            | -1                                                    | false      |
            | 0                                                     | false      |
            | 42                                                    | false      |
            | 42.1                                                  | false      |
            | 1.0                                                   | false      |
            | 0.0                                                   | false      |
            | -1.0                                                  | false      |
            | INF                                                   | false      |
            | -INF                                                  | false      |
            | NAN                                                   | false      |
            | "1"                                                   | false      |
            | "0"                                                   | false      |
            | "string"                                              | false      |
            | "true"                                                | false      |
            | "false"                                               | false      |
            | ""                                                    | false      |
            | null                                                  | false      |
            | true                                                  | true       |
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
            | value                                                 | result                                 |
            | 1                                                     | true                                   |
            | -1                                                    | true                                   |
            | 0                                                     | <error: Passed value 0 is invalid>     |
            | 42                                                    | true                                   |
            | 42.1                                                  | true                                   |
            | 1.0                                                   | true                                   |
            | 0.0                                                   | <error: Passed value 0 is invalid>     |
            | -1.0                                                  | true                                   |
            | INF                                                   | true                                   |
            | -INF                                                  | true                                   |
            | NAN                                                   | true                                   |
            | "1"                                                   | true                                   |
            | "0"                                                   | <error: Passed value "0" is invalid>   |
            | "string"                                              | true                                   |
            | "true"                                                | true                                   |
            | "false"                                               | true                                   |
            | ""                                                    | <error: Passed value "" is invalid>    |
            | null                                                  | <error: Passed value null is invalid>  |
            | true                                                  | true                                   |
            | false                                                 | <error: Passed value false is invalid> |
            | []                                                    | <error: Passed value [] is invalid>    |
            | [1]                                                   | true                                   |
            | (object)[]                                            | true                                   |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | true                                   |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | true                                   |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | true                                   |

    Scenario Outline: Denormalize "<value>"
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                                                 | result                                                                 |
            | 1                                                     | <error: Passed value 1 is invalid>                                     |
            | 0                                                     | <error: Passed value 0 is invalid>                                     |
            | -1                                                    | <error: Passed value -1 is invalid>                                    |
            | 42                                                    | <error: Passed value 42 is invalid>                                    |
            | 42.1                                                  | <error: Passed value 42.1 is invalid>                                  |
            | 1.0                                                   | <error: Passed value 1 is invalid>                                     |
            | 0.0                                                   | <error: Passed value 0 is invalid>                                     |
            | -1.0                                                  | <error: Passed value -1 is invalid>                                    |
            | INF                                                   | <error: Passed value INF is invalid>                                   |
            | -INF                                                  | <error: Passed value -INF is invalid>                                  |
            | NAN                                                   | <error: Passed value NAN is invalid>                                   |
            | "1"                                                   | <error: Passed value "1" is invalid>                                   |
            | "0"                                                   | <error: Passed value "0" is invalid>                                   |
            | "string"                                              | <error: Passed value "string" is invalid>                              |
            | "true"                                                | <error: Passed value "true" is invalid>                                |
            | "false"                                               | <error: Passed value "false" is invalid>                               |
            | ""                                                    | <error: Passed value "" is invalid>                                    |
            | null                                                  | <error: Passed value null is invalid>                                  |
            | true                                                  | true                                                                   |
            | false                                                 | <error: Passed value false is invalid>                                 |
            | []                                                    | <error: Passed value [] is invalid>                                    |
            | [1]                                                   | <error: Passed value [1] is invalid>                                   |
            | (object)[]                                            | <error: Passed value {} is invalid>                                    |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |