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
    - [01.custom-type-printer](/example/02.errors/01.custom-type-printer.php)
    - [02.extended-type-printer](/example/02.errors/02.extended-type-printer.php)
    - [03.custom-path-printer](/example/02.errors/03.custom-value-printer.php)
    - [04.custom-value-printer](/example/02.errors/04.custom-path-printer.php)
- [03.types](/example/03.types)
    - [01.type-platforms](/example/03.types/01.type-platforms.php)
    - [02.custom-type](/example/03.types/02.custom-type.php)
    - [03.custom-type-template-arguments](/example/03.types/03.custom-type-template-arguments.php)
    - [04.custom-platform](/example/03.types/04.custom-platform.php)
    - [05.custom-type-callable](/example/03.types/05.custom-type-callable.php)
    - [06.custom-type-psr-container](/example/03.types/06.custom-type-psr-container.php)
- [04.mapping-readers](/example/04.mapping-readers)
  - [01.reflection-mapping.php](/example/04.mapping-readers/01.reflection-mapping.php)
  - [02.attribute-mapping.php](/example/04.mapping-readers/02.attribute-mapping.php)
  - [03.phpdoc-mapping.php](/example/04.mapping-readers/03.phpdoc-mapping.php)
  - [04.array-mapping.php](/example/04.mapping-readers/04.array-mapping.php)
  - [05.php-config-mapping.php](/example/04.mapping-readers/05.php-config-mapping.php)
  - [06.yaml-config-mapping.php](/example/04.mapping-readers/06.yaml-config-mapping.php)
  - [07.neon-config-mapping.php](/example/04.mapping-readers/07.neon-config-mapping.php)
  - [08.inheritance.php](/example/04.mapping-readers/08.inheritance.php)
- [05.mapping-providers](/example/05.mapping-providers)
  - [01.default-provider.php](/example/05.mapping-providers/01.default-provider.php)
  - [02.in-memory-provider.php](/example/05.mapping-providers/02.in-memory-provider.php)
  - [03.psr6-provider.php](/example/05.mapping-providers/03.psr6-provider.php)
  - [04.psr16-provider.php](/example/05.mapping-providers/04.psr16-provider.php)
  - [05.second-level-cache.php](/example/05.mapping-providers/05.second-level-cache.php)
  - [06.inheritance.php](/example/05.mapping-providers/06.inheritance.php)

> Full documentation in progress...

## Installation

Mapper package is available as Composer repository and can be installed
using the following command in a root of your project:

```sh
composer require type-lang/mapper
```

## Quick Start

```php
use TypeLang\Mapper\Mapping\MapType;

class ExampleObject
{
    public function __construct(
        #[MapType('list<non-empty-string>')]
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

## Benchmarks

Results [here](https://github.com/php-type-language/mapper/actions/workflows/bench.yml)
like [this](https://github.com/php-type-language/mapper/actions/runs/11924690471/job/33235475673#step:6:10).

Sample: An object that contains a collection of objects, which contains
another collection of objects.

```php
object<ExampleObject>{
    name: string,
    items: list<ExampleObject>
}
```

The results are sorted by mode time.

### Denormalization

Denormalization: For example, conversion from JSON to PHP Object.

| benchmark                       | memory    | min       | mode        | rstdev |
|---------------------------------|-----------|-----------|-------------|--------|
| TypeLangStrictAttributesBench   | 1.929mb   | 91.180μs  | 105.264μs   | ±6.99% |
| TypeLangStrictPhpDocBench       | 2.048mb   | 98.860μs  | 108.488μs   | ±4.40% |
| TypeLangPhpDocBench             | 2.050mb   | 103.380μs | 106.407μs   | ±5.91% |
| TypeLangAttributesBench         | 1.930mb   | 104.490μs | 113.736μs   | ±4.23% |
| JMSAttributesBench              | 3.141mb   | 137.040μs | 142.373μs   | ±6.78% |
| ValinorBench                    | 2.701mb   | 227.370μs | 252.458μs   | ±4.59% |
| SymfonyPHPStanExtractorBench    | 3.678mb   | 371.650μs | 392.908μs   | ±2.86% |
| SymfonyPhpDocExtractorBench     | 4.027mb   | 379.740μs | 387.303μs   | ±3.21% |

### Normalization

Normalization: For example, conversion from PHP Object to JSON.

| benchmark                       | memory    | min         | mode      | rstdev   |
|---------------------------------|-----------|-------------|-----------|----------|
| ValinorBench                    | 1.966mb   | 62.570μs    | 69.184μs  | ±3.99%   |
| TypeLangStrictAttributesBench   | 1.845mb   | 81.090μs    | 88.343μs  | ±6.75%   |
| TypeLangPhpDocBench             | 1.965mb   | 81.800μs    | 90.504μs  | ±5.99%   |
| TypeLangAttributesBench         | 1.891mb   | 85.320μs    | 89.900μs  | ±5.15%   |
| TypeLangStrictPhpDocBench       | 1.967mb   | 89.920μs    | 94.865μs  | ±2.62%   |
| SymfonyPHPStanExtractorBench    | 2.086mb   | 93.160μs    | 100.173μs | ±5.41%   |
| SymfonyPhpDocExtractorBench     | 2.405mb   | 96.290μs    | 98.729μs  | ±4.17%   |
| JMSAttributesBench              | 3.770mb   | 131.420μs   | 150.310μs | ±5.85%   |
