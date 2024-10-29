Feature: Test BoolType
    Background:
        Given type "TypeLang\Mapper\Type\BoolType"

    Scenario: Matching
         When normalize
         Then matching returns the following values:
            | 42            | false |
            | 42.1          | false |
            | INF           | false |
            | NAN           | false |
            | "string"      | false |
            | null          | false |
            | (object)[]    | false |
            | []            | false |
            | true          | true  |
            | false         | true  |
         When denormalize
         Then matching returns the following values:
            | 42            | false |
            | 42.1          | false |
            | INF           | false |
            | NAN           | false |
            | "string"      | false |
            | null          | false |
            | (object)[]    | false |
            | []            | false |
            | true          | true  |
            | false         | true  |

    Scenario: Casting
        When normalize
        Then casting returns the following values:
            | 42            | <error: Passed value 42 is invalid>       |
            | 42.1          | <error: Passed value 42.1 is invalid>     |
            | INF           | <error: Passed value INF is invalid>      |
            | NAN           | <error: Passed value NAN is invalid>      |
            | "string"      | <error: Passed value "string" is invalid> |
            | null          | <error: Passed value null is invalid>     |
            | (object)[]    | <error: Passed value {} is invalid>       |
            | []            | <error: Passed value [] is invalid>       |
            | true          | true                                      |
            | false         | false                                     |
        When denormalize
        Then casting returns the following values:
            | 42            | <error: Passed value 42 is invalid>       |
            | 42.1          | <error: Passed value 42.1 is invalid>     |
            | INF           | <error: Passed value INF is invalid>      |
            | NAN           | <error: Passed value NAN is invalid>      |
            | "string"      | <error: Passed value "string" is invalid> |
            | null          | <error: Passed value null is invalid>     |
            | (object)[]    | <error: Passed value {} is invalid>       |
            | []            | <error: Passed value [] is invalid>       |
            | true          | true                                      |
            | false         | false                                     |
