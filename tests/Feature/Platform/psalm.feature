Feature: Checking for presence of type definitions in the Psalm Platform

    Background:
        Given platform "TypeLang\Mapper\Platform\StandardPlatform"

    Scenario Outline: Presence of "<type>" type
        Given type statement "<type>"
        Then the type must be defined
        Examples:
            | type                       | reason             |
            | int                        | Psalm Compat (5.x) |
            | float                      | Psalm Compat (5.x) |
            | string                     | Psalm Compat (5.x) |
            | bool                       | Psalm Compat (5.x) |
            # TODO | void                       | Psalm Compat (5.x) |
            | array-key                  | Psalm Compat (5.x) |
            | iterable                   | Psalm Compat (5.x) |
            # TODO | never                      | Psalm Compat (5.x) |
            # TODO | never-return               | Psalm Compat (5.x) |
            # TODO | never-returns              | Psalm Compat (5.x) |
            # TODO | no-return                  | Psalm Compat (5.x) |
            # TODO | empty                      | Psalm Compat (5.x) |
            | object                     | Psalm Compat (5.x) |
            # TODO | callable                   | Psalm Compat (5.x) |
            # TODO | pure-callable              | Psalm Compat (5.x) |
            | array                      | Psalm Compat (5.x) |
            # TODO | associative-array          | Psalm Compat (5.x) |
            # TODO | non-empty-array            | Psalm Compat (5.x) |
            # TODO | callable-array             | Psalm Compat (5.x) |
            | list                       | Psalm Compat (5.x) |
            # TODO | non-empty-list             | Psalm Compat (5.x) |
            # TODO | non-empty-string           | Psalm Compat (5.x) |
            # TODO | truthy-string              | Psalm Compat (5.x) |
            # TODO | non-falsy-string           | Psalm Compat (5.x) |
            # TODO | lowercase-string           | Psalm Compat (5.x) |
            # TODO | non-empty-lowercase-string | Psalm Compat (5.x) |
            # TODO | resource                   | Psalm Compat (5.x) |
            # TODO | resource (closed)          | Psalm Compat (5.x) |
            # TODO | closed-resource            | Psalm Compat (5.x) |
            # TODO | positive-int               | Psalm Compat (5.x) |
            # TODO | non-positive-int           | Psalm Compat (5.x) |
            # TODO | negative-int               | Psalm Compat (5.x) |
            # TODO | non-negative-int           | Psalm Compat (5.x) |
            # TODO | numeric                    | Psalm Compat (5.x) |
            | true                       | Psalm Compat (5.x) |
            | false                      | Psalm Compat (5.x) |
            # TODO | scalar                     | Psalm Compat (5.x) |
            | null                       | Psalm Compat (5.x) |
            | mixed                      | Psalm Compat (5.x) |
            # TODO | callable-object            | Psalm Compat (5.x) |
            # TODO | stringable-object          | Psalm Compat (5.x) |
            # TODO | class-string               | Psalm Compat (5.x) |
            # TODO | interface-string           | Psalm Compat (5.x) |
            # TODO | enum-string                | Psalm Compat (5.x) |
            # TODO | trait-string               | Psalm Compat (5.x) |
            # TODO | callable-string            | Psalm Compat (5.x) |
            # TODO | numeric-string             | Psalm Compat (5.x) |
            # TODO | literal-string             | Psalm Compat (5.x) |
            # TODO | non-empty-literal-string   | Psalm Compat (5.x) |
            # TODO | literal-int                | Psalm Compat (5.x) |
            # TODO | $this                      | Psalm Compat (5.x) |
            # TODO | non-empty-scalar           | Psalm Compat (5.x) |
            # TODO | empty-scalar               | Psalm Compat (5.x) |
            # TODO | non-empty-mixed            | Psalm Compat (5.x) |
            # TODO | Closure                    | Psalm Compat (5.x) |
            | traversable                | Psalm Compat (5.x) |
            # TODO | countable                  | Psalm Compat (5.x) |
            # TODO | arrayaccess                | Psalm Compat (5.x) |
            # TODO | pure-closure               | Psalm Compat (5.x) |
            | boolean                    | Psalm Compat (5.x) |
            | integer                    | Psalm Compat (5.x) |
            | double                     | Psalm Compat (5.x) |
            | real                       | Psalm Compat (5.x) |
            # TODO | self                       | Psalm Compat (5.x) |
            # TODO | static                     | Psalm Compat (5.x) |
            # TODO | key-of                     | Psalm Compat (5.x) |
            # TODO | value-of                   | Psalm Compat (5.x) |
            # TODO | properties-of              | Psalm Compat (5.x) |
            # TODO | public-properties-of       | Psalm Compat (5.x) |
            # TODO | protected-properties-of    | Psalm Compat (5.x) |
            # TODO | private-properties-of      | Psalm Compat (5.x) |
            # TODO | non-empty-countable        | Psalm Compat (5.x) |
            # TODO | class-string-map           | Psalm Compat (5.x) |
            # TODO | open-resource              | Psalm Compat (5.x) |
            # TODO | arraylike-object           | Psalm Compat (5.x) |
            # TODO | int-mask                   | Psalm Compat (5.x) |
            # TODO | int-mask-of                | Psalm Compat (5.x) |

