Feature: Test MixedType
    Background:
        Given type "TypeLang\Mapper\Type\MixedType"

    Scenario: Matching
         When normalize
         Then matching returns the following values:
            | 42                        | true |
            | 42.1                      | true |
            | INF                       | true |
            | NAN                       | true |
            | "string"                  | true |
            | null                      | true |
            | (object)[]                | true |
            | []                        | true |
            | true                      | true |
            | false                     | true |
         When denormalize
         Then matching returns the following values:
            | 42                        | true |
            | 42.1                      | true |
            | INF                       | true |
            | NAN                       | true |
            | "string"                  | true |
            | null                      | true |
            | (object)[]                | true |
            | []                        | true |
            | true                      | true |
            | false                     | true |

    Scenario: Casting
        When normalize
        Then casting returns the following values:
           | 42                        | 42                     |
           | 42.1                      | 42.1                   |
           | INF                       | INF                    |
           | NAN                       | NAN                    |
           | "string"                  | "string"               |
           | null                      | null                   |
           | (object)[]                | []                     |
           | []                        | []                     |
           | true                      | true                   |
           | false                     | false                  |
        When denormalize
        Then casting returns the following values:
           | 42                        | 42                     |
           | 42.1                      | 42.1                   |
           | INF                       | INF                    |
           | NAN                       | NAN                    |
           | "string"                  | "string"               |
           | null                      | null                   |
           | (object)[]                | (object)[]             |
           | []                        | []                     |
           | true                      | true                   |
           | false                     | false                  |
