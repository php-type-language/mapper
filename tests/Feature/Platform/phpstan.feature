Feature: Checking for presence of type definitions in the PHPStan Platform

    Background:
        Given todo

    Scenario Outline: Presence of "<type>" type
        Given type statement "<type>"
        Then the type must be defined
        Examples:
            | type                       | reason               |
            | int                        | PHPStan Compat (2.x) |
            | integer                    | PHPStan Compat (2.x) |
            | positive-int               | PHPStan Compat (2.x) |
            | negative-int               | PHPStan Compat (2.x) |
            | non-positive-int           | PHPStan Compat (2.x) |
            | non-negative-int           | PHPStan Compat (2.x) |
            | non-zero-int               | PHPStan Compat (2.x) |
            | string                     | PHPStan Compat (2.x) |
            | lowercase-string           | PHPStan Compat (2.x) |
            | literal-string             | PHPStan Compat (2.x) |
            | class-string               | PHPStan Compat (2.x) |
            | interface-string           | PHPStan Compat (2.x) |
            | trait-string               | PHPStan Compat (2.x) |
            | enum-string                | PHPStan Compat (2.x) |
            | callable-string            | PHPStan Compat (2.x) |
            | array-key                  | PHPStan Compat (2.x) |
            | scalar                     | PHPStan Compat (2.x) |
            | empty-scalar               | PHPStan Compat (2.x) |
            | non-empty-scalar           | PHPStan Compat (2.x) |
            | number                     | PHPStan Compat (2.x) |
            | numeric                    | PHPStan Compat (2.x) |
            | numeric-string             | PHPStan Compat (2.x) |
            | non-empty-string           | PHPStan Compat (2.x) |
            | non-empty-lowercase-string | PHPStan Compat (2.x) |
            | truthy-string              | PHPStan Compat (2.x) |
            | non-falsy-string           | PHPStan Compat (2.x) |
            | non-empty-literal-string   | PHPStan Compat (2.x) |
            | bool                       | PHPStan Compat (2.x) |
            | boolean                    | PHPStan Compat (2.x) |
            | true                       | PHPStan Compat (2.x) |
            | false                      | PHPStan Compat (2.x) |
            | null                       | PHPStan Compat (2.x) |
            | float                      | PHPStan Compat (2.x) |
            | double                     | PHPStan Compat (2.x) |
            | array                      | PHPStan Compat (2.x) |
            | associative-array          | PHPStan Compat (2.x) |
            | non-empty-array            | PHPStan Compat (2.x) |
            | iterable                   | PHPStan Compat (2.x) |
            | callable                   | PHPStan Compat (2.x) |
            | pure-callable              | PHPStan Compat (2.x) |
            | pure-closure               | PHPStan Compat (2.x) |
            | resource                   | PHPStan Compat (2.x) |
            | open-resource              | PHPStan Compat (2.x) |
            | closed-resource            | PHPStan Compat (2.x) |
            | mixed                      | PHPStan Compat (2.x) |
            | non-empty-mixed            | PHPStan Compat (2.x) |
            | void                       | PHPStan Compat (2.x) |
            | object                     | PHPStan Compat (2.x) |
            | callable-object            | PHPStan Compat (2.x) |
            | callable-array             | PHPStan Compat (2.x) |
            | never                      | PHPStan Compat (2.x) |
            | noreturn                   | PHPStan Compat (2.x) |
            | never-return               | PHPStan Compat (2.x) |
            | never-returns              | PHPStan Compat (2.x) |
            | no-return                  | PHPStan Compat (2.x) |
            | list                       | PHPStan Compat (2.x) |
            | non-empty-list             | PHPStan Compat (2.x) |
            | empty                      | PHPStan Compat (2.x) |
            | __stringandstringable      | PHPStan Compat (2.x) |
            | self                       | PHPStan Compat (2.x) |
            | static                     | PHPStan Compat (2.x) |
            | parent                     | PHPStan Compat (2.x) |
            | key-of                     | PHPStan Compat (2.x) |
            | value-of                   | PHPStan Compat (2.x) |
            | int-mask-of                | PHPStan Compat (2.x) |
            | int-mask                   | PHPStan Compat (2.x) |
            | __benevolent               | PHPStan Compat (2.x) |
            | template-type              | PHPStan Compat (2.x) |
            | new                        | PHPStan Compat (2.x) |

