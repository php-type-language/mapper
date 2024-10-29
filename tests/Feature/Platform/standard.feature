Feature: Checking for presence of type definitions in the Standard Platform

    Background:
        Given platform "TypeLang\Mapper\Platform\StandardPlatform"

    Scenario Outline: Presence of "<type>" type
        Given type statement "<type>"
        Then the type must be defined
        Examples:
            | type                                                             | reason         |
            | self                                                             | PHP 5.0+       |
            | parent                                                           | PHP 5.0+       |

            | array                                                            | PHP 5.1+       |
            | TypeLang\Mapper\Tests\Feature\Platform\Stub\ObjectStub           | PHP 5.1+       |
            | TypeLang\Mapper\Tests\Feature\Platform\Stub\InterfaceStub        | PHP 5.1+       |

            | callable                                                         | PHP 5.4+       |

            | int                                                              | PHP 7.0+       |
            | integer                                                          | PHP 7.0+ alias |
            | bool                                                             | PHP 7.0+       |
            | boolean                                                          | PHP 7.0+ alias |
            | float                                                            | PHP 7.0+       |
            | real                                                             | PHP 7.0+ alias |
            | double                                                           | PHP 7.0+ alias |
            | string                                                           | PHP 7.0+       |

            | void                                                             | PHP 7.1+       |
            | ?int                                                             | PHP 7.1+       |
            | iterable                                                         | PHP 7.1+       |

            | object                                                           | PHP 7.2+       |

            | int\|false                                                       | PHP 8.0+       |
            | static                                                           | PHP 8.0+       |
            | mixed                                                            | PHP 8.0+       |

            | never                                                            | PHP 8.1+       |
            | int&string                                                       | PHP 8.1+       |
            | TypeLang\Mapper\Tests\Feature\Platform\Stub\UnitEnumStub         | PHP 8.1+       |
            | TypeLang\Mapper\Tests\Feature\Platform\Stub\IntBackedEnumStub    | PHP 8.1+       |
            | TypeLang\Mapper\Tests\Feature\Platform\Stub\StringBackedEnumStub | PHP 8.1+       |

            | null                                                             | PHP 8.2+       |
            | false                                                            | PHP 8.2+       |
            | true                                                             | PHP 8.2+       |
            | int\|(true&int)                                                  | PHP 8.2+       |

