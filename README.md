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

You can see some [examples here](/example):

- [01.normalization](/example/01.normalization)
  - [01.object-normalization](/example/01.normalization/01.object-normalization.php)
  - [02.date-normalization](/example/01.normalization/02.date-normalization.php)
  - [03.date-format-normalization](/example/01.normalization/03.date-format-normalization.php)
  - [04.untyped-object-normalization](/example/01.normalization/04.untyped-object-normalization.php)
  - [05.typed-object-normalization](/example/01.normalization/05.typed-object-normalization.php)
  - [06.inherited-types-normalization](/example/01.normalization/06.inherited-types-normalization.php)
  - [07.collection-types-normalization](/example/01.normalization/07.collection-types-normalization.php)
  - [08.object-output-normalization](/example/01.normalization/08.object-output-normalization.php)
- [02.errors](/example/02.errors)
  - [01.errors-purification](/example/02.errors/01.errors-purification.php)
  - [02.custom-type-printer](/example/02.errors/02.custom-type-printer.php)
  - [03.extended-type-printer](/example/02.errors/03.extended-type-printer.php)
  - [04.custom-path-printer](/example/02.errors/04.custom-path-printer.php)
  - [05.custom-value-printer](/example/02.errors/05.custom-value-printer.php)
- [03.types](/example/03.types)
  - [01.type-platforms](/example/03.types/01.type-platforms.php)
  - [02.custom-type](/example/03.types/02.custom-type.php)
  - [03.custom-type-template-arguments](/example/03.types/03.custom-type-template-arguments.php)
  - [04.custom-platform](/example/03.types/04.custom-platform.php)
- [04.mapping](/example/04.mapping)
  - [01.reflection-mapping](/example/04.mapping/01.reflection-mapping.php)
  - [02.attribute-mapping](/example/04.mapping/02.attribute-mapping.php)
  - [03.driver-inheritance-mapping](/example/04.mapping/03.driver-inheritance-mapping.php)
  - [04.phpdoc-mapping](/example/04.mapping/04.phpdoc-mapping.php)
  - [05.phpdoc-custom-tags-mapping](/example/04.mapping/05.phpdoc-custom-tags-mapping.php)
  - [06.cache](/example/04.mapping/06.cache.php)
  
> Full documentation in progress...

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
        public readonly array $names,
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
