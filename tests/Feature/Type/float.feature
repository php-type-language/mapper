Feature: Test FloatType
    Background:
        Given type "TypeLang\Mapper\Type\FloatType"

    Scenario: Matching
         When normalize
         Then matching returns the following values:
            | 42            | true  |
            | 42.1          | true  |
            | INF           | true  |
            | NAN           | true  |
            | "string"      | false |
            | null          | false |
            | (object)[]    | false |
            | []            | false |
            | true          | false |
            | false         | false |
         When denormalize
         Then matching returns the following values:
            | 42            | true  |
            | 42.1          | true  |
            | INF           | true  |
            | NAN           | true  |
            | "string"      | false |
            | null          | false |
            | (object)[]    | false |
            | []            | false |
            | true          | false |
            | false         | false |

    Scenario: Casting
        When normalize
        Then casting returns the following values:
            | 42            | 42.0                                      |
            | 42.1          | 42.1                                      |
            | .1            | 0.1                                       |
            | -.1           | -0.1                                      |
            | 1.            | 1.0                                       |
            | -1.           | -1.0                                      |
            | 1e10          | 10000000000.0                             |
            | INF           | INF                                       |
            | -INF          | -INF                                      |
            | NAN           | NAN                                       |
            | "string"      | <error: Passed value "string" is invalid> |
            | null          | <error: Passed value null is invalid>     |
            | (object)[]    | <error: Passed value {} is invalid>       |
            | []            | <error: Passed value [] is invalid>       |
            | true          | <error: Passed value true is invalid>     |
            | false         | <error: Passed value false is invalid>    |
        When denormalize
        Then casting returns the following values:
            | 42            | 42.0                                      |
            | 42.1          | 42.1                                      |
            | .1            | 0.1                                       |
            | -.1           | -0.1                                      |
            | 1.            | 1.0                                       |
            | -1.           | -1.0                                      |
            | 1e10          | 10000000000.0                             |
            | INF           | INF                                       |
            | -INF          | -INF                                      |
            | NAN           | NAN                                       |
            | "string"      | <error: Passed value "string" is invalid> |
            | null          | <error: Passed value null is invalid>     |
            | (object)[]    | <error: Passed value {} is invalid>       |
            | []            | <error: Passed value [] is invalid>       |
            | true          | <error: Passed value true is invalid>     |
            | false         | <error: Passed value false is invalid>    |
