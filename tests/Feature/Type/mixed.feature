Feature: Checking the "MixedType" type behavior

    Background:
        Given type "TypeLang\Mapper\Type\MixedType"

    Scenario Outline: Normalization matching "<value>" by the MixedType
        When normalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value      | is_matched |
            | 42         | true       |
            | 42.1       | true       |
            | INF        | true       |
            | NAN        | true       |
            | "string"   | true       |
            | null       | true       |
            | (object)[] | true       |
            | []         | true       |
            | true       | true       |
            | false      | true       |

    Scenario Outline: Denormalization matching "<value>" by the MixedType
        When denormalize
        Then match of "<value>" must return <is_matched>
        Examples:
            | value      | is_matched |
            | 42         | true       |
            | 42.1       | true       |
            | INF        | true       |
            | NAN        | true       |
            | "string"   | true       |
            | null       | true       |
            | (object)[] | true       |
            | []         | true       |
            | true       | true       |
            | false      | true       |

    Scenario Outline: Normalization casting "<value>" by the MixedType
        When normalize
        Then cast of "<value>" must return <result>
        Examples:
            | value      | result   |
            | 42         | 42       |
            | 42.1       | 42.1     |
            | INF        | INF      |
            | NAN        | NAN      |
            | "string"   | "string" |
            | null       | null     |
            | (object)[] | []       |
            | []         | []       |
            | true       | true     |
            | false      | false    |

    Scenario Outline: Denormalization casting "<value>" by the MixedType
        When denormalize
        Then cast of "<value>" must return <result>
        Examples:
            | value      | result     |
            | 42         | 42         |
            | 42.1       | 42.1       |
            | INF        | INF        |
            | NAN        | NAN        |
            | "string"   | "string"   |
            | null       | null       |
            | (object)[] | (object)[] |
            | []         | []         |
            | true       | true       |
            | false      | false      |
