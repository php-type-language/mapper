<p align="center">
    <a href="https://packagist.org/packages/serafim/mapper"><img src="https://poser.pugx.org/serafim/mapper/require/php?style=for-the-badge" alt="PHP 8.1+"></a>
    <a href="https://packagist.org/packages/serafim/mapper"><img src="https://poser.pugx.org/serafim/mapper/version?style=for-the-badge" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/serafim/mapper"><img src="https://poser.pugx.org/serafim/mapper/v/unstable?style=for-the-badge" alt="Latest Unstable Version"></a>
    <a href="https://raw.githubusercontent.com/SerafimArts/Mapper/blob/master/LICENSE"><img src="https://poser.pugx.org/serafim/mapper/license?style=for-the-badge" alt="License MIT"></a>
</p>
<p align="center">
    <a href="https://github.com/SerafimArts/Mapper/actions"><img src="https://github.com/SerafimArts/Mapper/workflows/tests/badge.svg"></a>
</p>

The best PHP mapper you've ever seen =)

1. You can see some [examples here](/example)
2. Full documentation in progress...

## Installation

Mapper package is available as Composer repository and can be installed
using the following command in a root of your project:

```sh
composer require serafim/mapper
```

## Quick Start

```php
class ExampleObject
{
    public function __construct(
        public readonly string $name,
    ) {}
}

$mapper = new \Serafim\Mapper\Mapper();

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
