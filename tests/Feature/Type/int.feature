Feature: Test IntType
    Background:
        Given type "TypeLang\Mapper\Type\IntType"

    Scenario: Normalization: Matching
         When match when normalize
         Then type "Int" must be matched
          And other types must not be matched

    Scenario: Normalization: Casting
         When normalize
         Then type "Int" is 3735928559
          And other types must fail

    Scenario: Denormalization: Matching
        When match when denormalize
        Then type "Int" must be matched
         And other types must not be matched

    Scenario: Denormalization: Casting
         When denormalize
         Then type "Int" is 3735928559
          And other types must fail
