Feature: Checking the "BoolType" type behavior
    Background:
        Given type "TypeLang\Mapper\Type\BoolType"

    Scenario Outline: Matching "<value>" by the BoolType
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value      | is_matched |
            | 42         | false      |
            | 42.1       | false      |
            | INF        | false      |
            | NAN        | false      |
            | "string"   | false      |
            | null       | false      |
            | (object)[] | false      |
            | []         | false      |
            | true       | true       |
            | false      | true       |

    Scenario Outline: Casting "<value>" by the BoolType
        When normalize
        Then cast of "<value>" must return <result>
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value      | result                                    |
            | 42         | <error: Passed value 42 is invalid>       |
            | 42.1       | <error: Passed value 42.1 is invalid>     |
            | INF        | <error: Passed value INF is invalid>      |
            | NAN        | <error: Passed value NAN is invalid>      |
            | "string"   | <error: Passed value "string" is invalid> |
            | null       | <error: Passed value null is invalid>     |
            | (object)[] | <error: Passed value {} is invalid>       |
            | []         | <error: Passed value [] is invalid>       |
            | true       | true                                      |
            | false      | false                                     |
