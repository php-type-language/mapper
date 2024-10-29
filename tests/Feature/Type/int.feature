Feature: Checking the "IntType" type behavior

    Background:
        Given type "TypeLang\Mapper\Type\IntType"

    Scenario Outline: Matching "<value>" by the IntType
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value                  | is_matched |
            | 42                     | true       |
            | 9223372036854775807    | true       |
            | -42                    | true       |
            | -9223372036854775807-1 | true       |
            | -9223372036854775808   | false      |
            | 42.1                   | false      |
            | INF                    | false      |
            | NAN                    | false      |
            | "string"               | false      |
            | null                   | false      |
            | (object)[]             | false      |
            | []                     | false      |
            | true                   | false      |
            | false                  | false      |

    Scenario Outline: Casting "<value>" by the IntType
        When normalize
        Then cast of "<value>" must return <result>
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value                  | result                                                |
            | 42                     | 42                                                    |
            | 9223372036854775807    | 9223372036854775807                                   |
            | -42                    | -42                                                   |
            | -9223372036854775807-1 | -9223372036854775807-1                                |
            | -9223372036854775808   | <error: Passed value -9.2233720368548E+18 is invalid> |
            | 42.1                   | <error: Passed value 42.1 is invalid>                 |
            | INF                    | <error: Passed value INF is invalid>                  |
            | NAN                    | <error: Passed value NAN is invalid>                  |
            | "string"               | <error: Passed value "string" is invalid>             |
            | null                   | <error: Passed value null is invalid>                 |
            | (object)[]             | <error: Passed value {} is invalid>                   |
            | []                     | <error: Passed value [] is invalid>                   |
            | true                   | <error: Passed value true is invalid>                 |
            | false                  | <error: Passed value false is invalid>                |
