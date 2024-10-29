Feature: Checking the "FloatType" type behavior

    Background:
        Given type "TypeLang\Mapper\Type\FloatType"

    Scenario Outline: Matching "<value>" by the FloatType
        When normalize
        Then match of "<value>" must return <is_matched>
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value      | is_matched |
            | 42         | true       |
            | 42.1       | true       |
            | INF        | true       |
            | NAN        | true       |
            | "string"   | false      |
            | null       | false      |
            | (object)[] | false      |
            | []         | false      |
            | true       | false      |
            | false      | false      |

    Scenario Outline: Casting "<value>" by the FloatType
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
