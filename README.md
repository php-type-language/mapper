<a href="https://github.com/php-type-language" target="_blank">
    <img align="center" src="https://github.com/php-type-language/.github/blob/master/assets/dark.png?raw=true">
</a>

---

<p align="center">
    <a href="https://packagist.org/packages/type-lang/mapper"><img src="https://poser.pugx.org/type-lang/mapper/require/php?style=for-the-badge" alt="PHP 8.1+"></a>
    <a href="https://packagist.org/packages/type-lang/mapper"><img src="https://poser.pugx.org/type-lang/mapper/version?style=for-the-badge" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/type-lang/mapper"><img src="https://poser.pugx.org/type-lang/mapper/v/unstable?style=for-the-badge" alt="Latest Unstable Version"></a>
    <a href="https://raw.githubusercontent.com/php-type-language/mapper/blob/master/LICENSE"><img src="https://poser.pugx.org/type-lang/mapper/license?style=for-the-badge" alt="License MIT"></a>
</p>
<p align="center">
    <a href="https://github.com/php-type-language/mapper/actions"><img src="https://github.com/php-type-language/mapper/workflows/tests/badge.svg"></a>
</p>

The best PHP mapper you've ever seen =)

1. You can see some [examples here](/example)
2. Full documentation in progress...

## Installation

Mapper package is available as Composer repository and can be installed
using the following command in a root of your project:

```sh
composer require type-lang/mapper
```

## Quick Start

```php
use TypeLang\Mapper\Mapping\MapProperty;

class ExampleObject
{
    public function __construct(
        #[MapProperty('list<non-empty-string>')]
        public readonly array $name,
    ) {}
}

$mapper = new \TypeLang\Mapper\Mapper();

$result = $mapper->normalize(
    new ExampleObject(['Example'])
);
// Expected Result:
//
// array:1 [
//   "names" => array:1 [
//     0 => "Example"
//   ]
// ]


$result = $mapper->denormalize([
    'names' => ['first', 'second']
], ExampleObject::class);
// Expected Result:
//
// ExampleObject {#324
//   +names: array:2 [
//     0 => "first"
//     1 => "second"
//   ]
// }


$result = $mapper->denormalize([
    'names' => ['first', 'second', ''],
], ExampleObject::class);
// Expected Result:
//
// InvalidFieldTypeValueException: Passed value of field "names" must be of type
//   list<non-empty-string>, but array(3)["first", "second", ""] given at $.names[2]
```
