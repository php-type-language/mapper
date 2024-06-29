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
class ExampleObject
{
    public function __construct(
        public readonly string $name,
    ) {}
}

$mapper = new \TypeLang\Mapper\Mapper();

$normalized = $mapper->normalize(new ExampleObject('Example'));
// Expected Result:
// array:1 [
//   "name" => "Example"
// ]

$denormalized = $mapper->denormalize($normalized, ExampleObject::class);
// Expected Result:
// ExampleObject {#14
//   +name: "Example"
// }
```
