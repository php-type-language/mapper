Feature: Checking for presence of type definitions in the Phan Platform

    Background:
        Given platform "TypeLang\Mapper\Platform\StandardPlatform"

    Scenario Outline: Presence of "<type>" type
        Given type statement "<type>"
        Then the type must be defined
        Examples:
            | type                        | reason            |
            | list                        | Phan Compat (5.x) |
            | integer                     | Phan Compat (5.x) |
            | string                      | Phan Compat (5.x) |
            | NULL                        | Phan Compat (5.x) |
            | double                      | Phan Compat (5.x) |
            | object                      | Phan Compat (5.x) |
            | boolean                     | Phan Compat (5.x) |
            | array                       | Phan Compat (5.x) |
            | iterable                    | Phan Compat (5.x) |
            | array-key                   | Phan Compat (5.x) |
            | bool                        | Phan Compat (5.x) |
            | false                       | Phan Compat (5.x) |
            | float                       | Phan Compat (5.x) |
            | int                         | Phan Compat (5.x) |
            | mixed                       | Phan Compat (5.x) |
            | null                        | Phan Compat (5.x) |
            | true                        | Phan Compat (5.x) |
            # TODO | class-string                | Phan Compat (5.x) |
            # TODO | associative-array           | Phan Compat (5.x) |
            # TODO | non-empty-associative-array | Phan Compat (5.x) |
            # TODO | non-empty-array             | Phan Compat (5.x) |
            # TODO | non-empty-list              | Phan Compat (5.x) |
            # TODO | non-empty-string            | Phan Compat (5.x) |
            # TODO | non-empty-lowercase-string  | Phan Compat (5.x) |
            # TODO | non-zero-int                | Phan Compat (5.x) |
            # TODO | resource                    | Phan Compat (5.x) |
            # TODO | callable                    | Phan Compat (5.x) |
            # TODO | callable-array              | Phan Compat (5.x) |
            # TODO | callable-object             | Phan Compat (5.x) |
            # TODO | callable-string             | Phan Compat (5.x) |
            # TODO | closure                     | Phan Compat (5.x) |
            # TODO | phan-intersection-type      | Phan Compat (5.x) |
            # TODO | non-empty-mixed             | Phan Compat (5.x) |
            # TODO | non-null-mixed              | Phan Compat (5.x) |
            # TODO | scalar                      | Phan Compat (5.x) |
            # TODO | lowercase-string            | Phan Compat (5.x) |
            # TODO | numeric-string              | Phan Compat (5.x) |
            # TODO | void                        | Phan Compat (5.x) |
            # TODO | never                       | Phan Compat (5.x) |
            # TODO | no-return                   | Phan Compat (5.x) |
            # TODO | never-return                | Phan Compat (5.x) |
            # TODO | never-returns               | Phan Compat (5.x) |
            # TODO | static                      | Phan Compat (5.x) |
            # TODO | $this                       | Phan Compat (5.x) |

