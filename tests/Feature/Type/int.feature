Feature: Test IntType
    Background:
        Given type "TypeLang\Mapper\Type\IntType"

    Scenario: Matching
         When normalize
         Then matching returns the following values:
            | 42                        | true  |
            | 9223372036854775807       | true  |
            | -42                       | true  |
            | -9223372036854775807-1    | true  |
            | -9223372036854775808      | false |
            | 42.1                      | false |
            | INF                       | false |
            | NAN                       | false |
            | "string"                  | false |
            | null                      | false |
            | (object)[]                | false |
            | []                        | false |
            | true                      | false |
            | false                     | false |
         When denormalize
         Then matching returns the following values:
            | 42                        | true  |
            | 9223372036854775807       | true  |
            | -42                       | true  |
            | -9223372036854775807-1    | true  |
            | -9223372036854775808      | false |
            | 42.1                      | false |
            | INF                       | false |
            | NAN                       | false |
            | "string"                  | false |
            | null                      | false |
            | (object)[]                | false |
            | []                        | false |
            | true                      | false |
            | false                     | false |

    Scenario: Casting
        When normalize
        Then casting returns the following values:
           | 42                        | 42                                                    |
           | 9223372036854775807       | 9223372036854775807                                   |
           | -42                       | -42                                                   |
           | -9223372036854775807-1    | -9223372036854775807-1                                |
           | -9223372036854775808      | <error: Passed value -9.2233720368548E+18 is invalid> |
           | 42.1                      | <error: Passed value 42.1 is invalid>                 |
           | INF                       | <error: Passed value INF is invalid>                  |
           | NAN                       | <error: Passed value NAN is invalid>                  |
           | "string"                  | <error: Passed value "string" is invalid>             |
           | null                      | <error: Passed value null is invalid>                 |
           | (object)[]                | <error: Passed value {} is invalid>                   |
           | []                        | <error: Passed value [] is invalid>                   |
           | true                      | <error: Passed value true is invalid>                 |
           | false                     | <error: Passed value false is invalid>                |
        When denormalize
        Then casting returns the following values:
           | 42                        | 42                                                    |
           | 9223372036854775807       | 9223372036854775807                                   |
           | -42                       | -42                                                   |
           | -9223372036854775807-1    | -9223372036854775807-1                                |
           | -9223372036854775808      | <error: Passed value -9.2233720368548E+18 is invalid> |
           | 42.1                      | <error: Passed value 42.1 is invalid>                 |
           | INF                       | <error: Passed value INF is invalid>                  |
           | NAN                       | <error: Passed value NAN is invalid>                  |
           | "string"                  | <error: Passed value "string" is invalid>             |
           | null                      | <error: Passed value null is invalid>                 |
           | (object)[]                | <error: Passed value {} is invalid>                   |
           | []                        | <error: Passed value [] is invalid>                   |
           | true                      | <error: Passed value true is invalid>                 |
           | false                     | <error: Passed value false is invalid>                |
