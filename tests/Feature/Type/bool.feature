Feature: Test BoolType
    Background:
        Given type "TypeLang\Mapper\Type\BoolType"

    Scenario: Normalization: Matching
         When match when normalize
         Then type "True" must be matched
          And type "False" must be matched
          And other types must not be matched

    Scenario: Normalization: Casting
         When normalize
         Then type "True" is true
          And type "False" is false
          And other types must fail

    Scenario: Denormalization: Matching
        When match when denormalize
        Then type "True" must be matched
         And type "False" must be matched
         And other types must not be matched

    Scenario: Denormalization: Casting
         When denormalize
         Then type "True" is true
          And type "False" is false
          And other types must fail
