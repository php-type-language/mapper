Feature: Checking for presence of type definitions in the Phan Platform

    Background:
        Given todo

    Scenario Outline: Presence of "<type>" type
        Given type statement "<type>"
        Then the type must be defined
        Examples:
            | type                        | reason            |
            | closure                     | Phan Compat (5.x) |
            | callable                    | Phan Compat (5.x) |
            | callable-object             | Phan Compat (5.x) |
            | callable-string             | Phan Compat (5.x) |
            | callable-array              | Phan Compat (5.x) |
            | lowercase-string            | Phan Compat (5.x) |
            | numeric-string              | Phan Compat (5.x) |
            | class-string                | Phan Compat (5.x) |
            | list                        | Phan Compat (5.x) |
            | associative-array           | Phan Compat (5.x) |
            | non-empty-associative-array | Phan Compat (5.x) |
            | non-empty-array             | Phan Compat (5.x) |
            | non-empty-list              | Phan Compat (5.x) |
            | non-empty-string            | Phan Compat (5.x) |
            | non-empty-lowercase-string  | Phan Compat (5.x) |
            | non-zero-int                | Phan Compat (5.x) |
            | integer                     | Phan Compat (5.x) |
            | string                      | Phan Compat (5.x) |
            | NULL                        | Phan Compat (5.x) |
            | double                      | Phan Compat (5.x) |
            | object                      | Phan Compat (5.x) |
            | boolean                     | Phan Compat (5.x) |
            | array                       | Phan Compat (5.x) |
            | resource                    | Phan Compat (5.x) |
            | array                       | Phan Compat (5.x) |
            | non-empty-array             | Phan Compat (5.x) |
            | associative-array           | Phan Compat (5.x) |
            | non-empty-associative-array | Phan Compat (5.x) |
            | list                        | Phan Compat (5.x) |
            | non-empty-list              | Phan Compat (5.x) |
            | iterable                    | Phan Compat (5.x) |
            | class-string                | Phan Compat (5.x) |
            | phan-intersection-type      | Phan Compat (5.x) |
            | array                       | Phan Compat (5.x) |
            | array-key                   | Phan Compat (5.x) |
            | associative-array           | Phan Compat (5.x) |
            | bool                        | Phan Compat (5.x) |
            | callable                    | Phan Compat (5.x) |
            | callable-array              | Phan Compat (5.x) |
            | callable-object             | Phan Compat (5.x) |
            | callable-string             | Phan Compat (5.x) |
            | class-string                | Phan Compat (5.x) |
            | closure                     | Phan Compat (5.x) |
            | false                       | Phan Compat (5.x) |
            | float                       | Phan Compat (5.x) |
            | int                         | Phan Compat (5.x) |
            | list                        | Phan Compat (5.x) |
            | phan-intersection-type      | Phan Compat (5.x) |
            | mixed                       | Phan Compat (5.x) |
            | non-empty-mixed             | Phan Compat (5.x) |
            | non-null-mixed              | Phan Compat (5.x) |
            | non-empty-array             | Phan Compat (5.x) |
            | non-empty-associative-array | Phan Compat (5.x) |
            | non-empty-list              | Phan Compat (5.x) |
            | non-empty-lowercase-string  | Phan Compat (5.x) |
            | non-empty-string            | Phan Compat (5.x) |
            | non-zero-int                | Phan Compat (5.x) |
            | null                        | Phan Compat (5.x) |
            | object                      | Phan Compat (5.x) |
            | resource                    | Phan Compat (5.x) |
            | scalar                      | Phan Compat (5.x) |
            | string                      | Phan Compat (5.x) |
            | lowercase-string            | Phan Compat (5.x) |
            | numeric-string              | Phan Compat (5.x) |
            | true                        | Phan Compat (5.x) |
            | void                        | Phan Compat (5.x) |
            | never                       | Phan Compat (5.x) |
            | no-return                   | Phan Compat (5.x) |
            | never-return                | Phan Compat (5.x) |
            | never-returns               | Phan Compat (5.x) |
            | iterable                    | Phan Compat (5.x) |
            | static                      | Phan Compat (5.x) |
            | $this                       | Phan Compat (5.x) |

