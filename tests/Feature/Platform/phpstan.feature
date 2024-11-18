Feature: Checking for presence of type definitions in the PHPStan Platform

    Background:
        Given platform "TypeLang\Mapper\Platform\StandardPlatform"

    Scenario Outline: Presence of "<type>" type
        Given type statement "<type>"
        Then the type must be defined
        Examples:
            | type                       | reason               |
            | int                        | PHPStan Compat (2.x) |
            | integer                    | PHPStan Compat (2.x) |
            # TODO | positive-int               | PHPStan Compat (2.x) |
            # TODO | negative-int               | PHPStan Compat (2.x) |
            # TODO | non-positive-int           | PHPStan Compat (2.x) |
            # TODO | non-negative-int           | PHPStan Compat (2.x) |
            # TODO | non-zero-int               | PHPStan Compat (2.x) |
            | string                     | PHPStan Compat (2.x) |
            # TODO | lowercase-string           | PHPStan Compat (2.x) |
            # TODO | literal-string             | PHPStan Compat (2.x) |
            # TODO | class-string               | PHPStan Compat (2.x) |
            # TODO | interface-string           | PHPStan Compat (2.x) |
            # TODO | trait-string               | PHPStan Compat (2.x) |
            # TODO | enum-string                | PHPStan Compat (2.x) |
            # TODO | callable-string            | PHPStan Compat (2.x) |
            | array-key                  | PHPStan Compat (2.x) |
            # TODO | scalar                     | PHPStan Compat (2.x) |
            # TODO | empty-scalar               | PHPStan Compat (2.x) |
            # TODO | non-empty-scalar           | PHPStan Compat (2.x) |
            # TODO | number                     | PHPStan Compat (2.x) |
            # TODO | numeric                    | PHPStan Compat (2.x) |
            # TODO | numeric-string             | PHPStan Compat (2.x) |
            # TODO | non-empty-string           | PHPStan Compat (2.x) |
            # TODO | non-empty-lowercase-string | PHPStan Compat (2.x) |
            # TODO | truthy-string              | PHPStan Compat (2.x) |
            # TODO | non-falsy-string           | PHPStan Compat (2.x) |
            # TODO | non-empty-literal-string   | PHPStan Compat (2.x) |
            | bool                       | PHPStan Compat (2.x) |
            | boolean                    | PHPStan Compat (2.x) |
            | true                       | PHPStan Compat (2.x) |
            | false                      | PHPStan Compat (2.x) |
            | null                       | PHPStan Compat (2.x) |
            | float                      | PHPStan Compat (2.x) |
            | double                     | PHPStan Compat (2.x) |
            | array                      | PHPStan Compat (2.x) |
            # TODO | associative-array          | PHPStan Compat (2.x) |
            # TODO | non-empty-array            | PHPStan Compat (2.x) |
            | iterable                   | PHPStan Compat (2.x) |
            # TODO | callable                   | PHPStan Compat (2.x) |
            # TODO | pure-callable              | PHPStan Compat (2.x) |
            # TODO | pure-closure               | PHPStan Compat (2.x) |
            # TODO | resource                   | PHPStan Compat (2.x) |
            # TODO | open-resource              | PHPStan Compat (2.x) |
            # TODO | closed-resource            | PHPStan Compat (2.x) |
            | mixed                      | PHPStan Compat (2.x) |
            # TODO | non-empty-mixed            | PHPStan Compat (2.x) |
            # TODO | void                       | PHPStan Compat (2.x) |
            | object                     | PHPStan Compat (2.x) |
            # TODO | callable-object            | PHPStan Compat (2.x) |
            # TODO | callable-array             | PHPStan Compat (2.x) |
            # TODO | never                      | PHPStan Compat (2.x) |
            # TODO | noreturn                   | PHPStan Compat (2.x) |
            # TODO | never-return               | PHPStan Compat (2.x) |
            # TODO | never-returns              | PHPStan Compat (2.x) |
            # TODO | no-return                  | PHPStan Compat (2.x) |
            | list                       | PHPStan Compat (2.x) |
            # TODO | non-empty-list             | PHPStan Compat (2.x) |
            # TODO | empty                      | PHPStan Compat (2.x) |
            # TODO | __stringandstringable      | PHPStan Compat (2.x) |
            # TODO | self                       | PHPStan Compat (2.x) |
            # TODO | static                     | PHPStan Compat (2.x) |
            # TODO | parent                     | PHPStan Compat (2.x) |
            # TODO | key-of                     | PHPStan Compat (2.x) |
            # TODO | value-of                   | PHPStan Compat (2.x) |
            # TODO | int-mask-of                | PHPStan Compat (2.x) |
            # TODO | int-mask                   | PHPStan Compat (2.x) |
            # TODO | __benevolent               | PHPStan Compat (2.x) |
            # TODO | template-type              | PHPStan Compat (2.x) |
            # TODO | new                        | PHPStan Compat (2.x) |

