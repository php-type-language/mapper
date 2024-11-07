Feature: Checking the "float" (TypeLang\Mapper\Type\FloatType) type behavior

    Background:
        Given type "TypeLang\Mapper\Type\FloatType"

    Scenario Outline: Matching "<value>"
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                                                 | is_matched |
            | 1                                                     | true       |
            | -1                                                    | true       |
            | 0                                                     | true       |
            | 42                                                    | true       |
            | 42.1                                                  | true       |
            | 1.0                                                   | true       |
            | 0.0                                                   | true       |
            | -1.0                                                  | true       |
            | INF                                                   | true       |
            | NAN                                                   | true       |
            | "1"                                                   | false      |
            | "0"                                                   | false      |
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

    Scenario Outline: Casting "<value>"
        When normalize
        Then cast of "<value>" must return <result>
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value      | result                                    |
            | 42         | 42.0                                      |
            | 42.1       | 42.1                                      |
            | .1         | 0.1                                       |
            | -.1        | -0.1                                      |
            | 1.         | 1.0                                       |
            | -1.        | -1.0                                      |
            | 1e10       | 10000000000.0                             |
            | INF        | INF                                       |
            | -INF       | -INF                                      |
            | NAN        | NAN                                       |
            | "string"   | <error: Passed value "string" is invalid> |
            | null       | <error: Passed value null is invalid>     |
            | (object)[] | <error: Passed value {} is invalid>       |
            | []         | <error: Passed value [] is invalid>       |
            | true       | <error: Passed value true is invalid>     |
            | false      | <error: Passed value false is invalid>    |
