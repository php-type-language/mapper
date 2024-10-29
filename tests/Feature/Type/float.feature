Feature: Test FloatType
    Background:
        Given type "TypeLang\Mapper\Type\FloatType"

    Scenario: Normalization: Matching
         When match when normalize
         Then type "Int" must be matched
          And type "Float" must be matched
          And type "Inf" must be matched
          And type "Nan" must be matched
          And other types must not be matched

    Scenario: Normalization: Casting
         When normalize
         Then type "Int" is 3735928559.0
          And type "Float" is 42.0
          And type "Inf" is inf
          And type "Nan" is nan
          And other types must fail

    Scenario: Denormalization: Matching
        When match when denormalize
        Then type "Int" must be matched
         And type "Float" must be matched
         And type "Inf" must be matched
         And type "Nan" must be matched
         And other types must not be matched

    Scenario: Denormalization: Casting
         When denormalize
         Then type "Int" is 3735928559.0
          And type "Float" is 42.0
          And type "Inf" is inf
          And type "Nan" is nan
          And other types must fail
