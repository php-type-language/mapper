Feature: Checking the "BoolType" type behavior

    Background:
        Given type "TypeLang\Mapper\Type\BoolType"

    Scenario Outline: Matching "<value>" by the BoolType
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            | 42                                                    | false      |
            | 42.1                                                  | false      |
            | INF                                                   | false      |
            | NAN                                                   | false      |
            | "string"                                              | false      |
            | null                                                  | false      |
            | true                                                  | true       |
            | false                                                 | true       |
            | []                                                    | false      |
            | (object)[]                                            | false      |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | false      |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | false      |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | false      |

    Scenario Outline: Normalize "<value>" by the BoolType
        When normalize
        Then cast of "<value>" must return <result>
        # Can be converted to a false:
        # - int(0)
        # - float(0.0)
        # - string("0")
        # - empty string ("")
        # - empty array ([])
        # - null
        # Otherwise true
        Examples:
            | value                                                 | result |
            | 1                                                     | true   |
            | -1                                                    | true   |
            | 0                                                     | false  |
            | 42                                                    | true   |
            | 42.1                                                  | true   |
            | 1.0                                                   | true   |
            | 0.0                                                   | false  |
            | -1.0                                                  | true   |
            | INF                                                   | true   |
            | NAN                                                   | true   |
            | "1"                                                   | true   |
            | "0"                                                   | false  |
            | "string"                                              | true   |
            | ""                                                    | false  |
            | null                                                  | false  |
            | true                                                  | true   |
            | false                                                 | false  |
            | []                                                    | false  |
            | [1]                                                   | true   |
            | (object)[]                                            | true   |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | true   |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | true   |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | true   |

    Scenario Outline: Denormalize "<value>" by the BoolType
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
            | NAN                                                   | <error: Passed value NAN is invalid>                                   |
            | "1"                                                   | <error: Passed value "1" is invalid>                                   |
            | "0"                                                   | <error: Passed value "0" is invalid>                                   |
            | "string"                                              | <error: Passed value "string" is invalid>                              |
            | ""                                                    | <error: Passed value "" is invalid>                                    |
            | null                                                  | <error: Passed value null is invalid>                                  |
            | true                                                  | true                                                                   |
            | false                                                 | false                                                                  |
            | []                                                    | <error: Passed value [] is invalid>                                    |
            | [1]                                                   | <error: Passed value [1] is invalid>                                   |
            | (object)[]                                            | <error: Passed value {} is invalid>                                    |
            | TypeLang\Mapper\Tests\Stub\IntBackedEnumStub::CASE    | <error: Passed value {"name": "CASE", "value": 3735928559} is invalid> |
            | TypeLang\Mapper\Tests\Stub\StringBackedEnumStub::CASE | <error: Passed value {"name": "CASE", "value": "case"} is invalid>     |
            | TypeLang\Mapper\Tests\Stub\UnitEnumStub::CASE         | <error: Passed value {"name": "CASE"} is invalid>                      |
