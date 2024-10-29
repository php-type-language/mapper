Feature: Checking for presence of type definitions in the Psalm Platform

    Background:
        Given todo

    Scenario Outline: Presence of "<type>" type
        Given type statement "<type>"
        Then the type must be defined
        Examples:
            | type                       | reason             |
            | int                        | Psalm Compat (5.x) |
            | float                      | Psalm Compat (5.x) |
            | string                     | Psalm Compat (5.x) |
            | bool                       | Psalm Compat (5.x) |
            | void                       | Psalm Compat (5.x) |
            | array-key                  | Psalm Compat (5.x) |
            | iterable                   | Psalm Compat (5.x) |
            | never                      | Psalm Compat (5.x) |
            | never-return               | Psalm Compat (5.x) |
            | never-returns              | Psalm Compat (5.x) |
            | no-return                  | Psalm Compat (5.x) |
            | empty                      | Psalm Compat (5.x) |
            | object                     | Psalm Compat (5.x) |
            | callable                   | Psalm Compat (5.x) |
            | pure-callable              | Psalm Compat (5.x) |
            | array                      | Psalm Compat (5.x) |
            | associative-array          | Psalm Compat (5.x) |
            | non-empty-array            | Psalm Compat (5.x) |
            | callable-array             | Psalm Compat (5.x) |
            | list                       | Psalm Compat (5.x) |
            | non-empty-list             | Psalm Compat (5.x) |
            | non-empty-string           | Psalm Compat (5.x) |
            | truthy-string              | Psalm Compat (5.x) |
            | non-falsy-string           | Psalm Compat (5.x) |
            | lowercase-string           | Psalm Compat (5.x) |
            | non-empty-lowercase-string | Psalm Compat (5.x) |
            | resource                   | Psalm Compat (5.x) |
            | resource (closed)          | Psalm Compat (5.x) |
            | closed-resource            | Psalm Compat (5.x) |
            | positive-int               | Psalm Compat (5.x) |
            | non-positive-int           | Psalm Compat (5.x) |
            | negative-int               | Psalm Compat (5.x) |
            | non-negative-int           | Psalm Compat (5.x) |
            | numeric                    | Psalm Compat (5.x) |
            | true                       | Psalm Compat (5.x) |
            | false                      | Psalm Compat (5.x) |
            | scalar                     | Psalm Compat (5.x) |
            | null                       | Psalm Compat (5.x) |
            | mixed                      | Psalm Compat (5.x) |
            | callable-object            | Psalm Compat (5.x) |
            | stringable-object          | Psalm Compat (5.x) |
            | class-string               | Psalm Compat (5.x) |
            | interface-string           | Psalm Compat (5.x) |
            | enum-string                | Psalm Compat (5.x) |
            | trait-string               | Psalm Compat (5.x) |
            | callable-string            | Psalm Compat (5.x) |
            | numeric-string             | Psalm Compat (5.x) |
            | literal-string             | Psalm Compat (5.x) |
            | non-empty-literal-string   | Psalm Compat (5.x) |
            | literal-int                | Psalm Compat (5.x) |
            | $this                      | Psalm Compat (5.x) |
            | non-empty-scalar           | Psalm Compat (5.x) |
            | empty-scalar               | Psalm Compat (5.x) |
            | non-empty-mixed            | Psalm Compat (5.x) |
            | Closure                    | Psalm Compat (5.x) |
            | traversable                | Psalm Compat (5.x) |
            | countable                  | Psalm Compat (5.x) |
            | arrayaccess                | Psalm Compat (5.x) |
            | pure-closure               | Psalm Compat (5.x) |
            | boolean                    | Psalm Compat (5.x) |
            | integer                    | Psalm Compat (5.x) |
            | double                     | Psalm Compat (5.x) |
            | real                       | Psalm Compat (5.x) |
            | self                       | Psalm Compat (5.x) |
            | static                     | Psalm Compat (5.x) |
            | key-of                     | Psalm Compat (5.x) |
            | value-of                   | Psalm Compat (5.x) |
            | properties-of              | Psalm Compat (5.x) |
            | public-properties-of       | Psalm Compat (5.x) |
            | protected-properties-of    | Psalm Compat (5.x) |
            | private-properties-of      | Psalm Compat (5.x) |
            | non-empty-countable        | Psalm Compat (5.x) |
            | class-string-map           | Psalm Compat (5.x) |
            | open-resource              | Psalm Compat (5.x) |
            | arraylike-object           | Psalm Compat (5.x) |
            | int-mask                   | Psalm Compat (5.x) |
            | int-mask-of                | Psalm Compat (5.x) |

