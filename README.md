<a href="https://github.com/php-type-language" target="_blank">
    <img align="center" src="https://github.com/php-type-language/.github/blob/master/assets/dark.png?raw=true">
</a>

---

<p align="center">
    <a href="https://packagist.org/packages/type-lang/mapper"><img src="https://poser.pugx.org/type-lang/mapper/require/php?style=for-the-badge" alt="PHP 8.1+"></a>
    <a href="https://packagist.org/packages/type-lang/mapper"><img src="https://poser.pugx.org/type-lang/mapper/version?style=for-the-badge" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/type-lang/mapper"><img src="https://poser.pugx.org/type-lang/mapper/v/unstable?style=for-the-badge" alt="Latest Unstable Version"></a>
    <a href="https://raw.githubusercontent.com/php-type-language/mapper/blob/master/LICENSE"><img src="https://poser.pugx.org/type-lang/mapper/license?style=for-the-badge" alt="License MIT"></a>
    <a href="https://github.com/xepozz/meta-storm-idea-plugin"><img src="https://img.shields.io/static/v1?&label=Powered+by&message=Meta+Storm+Plugin&logo=phpstorm&color=db5860&style=for-the-badge" alt="MetaStorm"></a>
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

```typescript
ExampleObject {
    name: string,
    items: list<ExampleObject>
}
```

The results are sorted by mode time.

### Denormalization

Denormalization: Transformation from raw payload (array) to concrete object.

| benchmark               | memory  | min       | max       | mode      | rstdev |
|-------------------------|---------|-----------|-----------|-----------|--------|
| TypeLangAttributesBench | 1.444mb | 141.800μs | 156.050μs | 145.760μs | ±2.13% |
| JMSAttributesBench      | 1.429mb | 144.400μs | 157.100μs | 146.736μs | ±2.12% |
| TypeLangDocBlockBench   | 1.642mb | 144.800μs | 153.850μs | 148.059μs | ±1.29% |
| ValinorBench            | 1.344mb | 217.550μs | 229.150μs | 220.319μs | ±1.41% |
| SymfonyDocBlockBench    | 2.163mb | 495.350μs | 507.950μs | 499.492μs | ±0.72% |
| SymfonyPHPStanBench     | 1.426mb | 506.650μs | 544.500μs | 510.798μs | ±1.53% |

### Denormalization + Cache

| benchmark               | memory  | min       | max       | mode      | rstdev |
|-------------------------|---------|-----------|-----------|-----------|--------|
| TypeLangDocBlockBench   | 1.544mb | 113.250μs | 125.350μs | 115.831μs | ±2.64% |
| JMSAttributesBench      | 1.429mb | 125.850μs | 148.750μs | 128.718μs | ±3.68% |
| TypeLangAttributesBench | 1.436mb | 170.100μs | 182.200μs | 173.155μs | ±1.70% |
| ValinorBench            | 1.257mb | 341.000μs | 374.450μs | 346.891μs | ±1.94% |
| SymfonyPHPStanBench     | 1.370mb | 583.600μs | 609.050μs | 590.473μs | ±0.88% |
| SymfonyDocBlockBench    | 2.163mb | 644.350μs | 686.550μs | 651.617μs | ±1.32% |

### Normalization

Normalization: Transformation from object to raw payload (array).

| benchmark               | memory  | min       | max       | mode      | rstdev |
|-------------------------|---------|-----------|-----------|-----------|--------|
| JMSAttributesBench      | 1.476mb | 93.550μs  | 125.100μs | 112.011μs | ±9.21% |
| TypeLangDocBlockBench   | 1.643mb | 110.650μs | 133.000μs | 112.881μs | ±4.25% |
| SymfonyPHPStanBench     | 1.370mb | 112.850μs | 121.850μs | 115.140μs | ±1.89% |
| TypeLangAttributesBench | 1.444mb | 117.300μs | 127.250μs | 120.649μs | ±2.43% |
| ValinorBench            | 1.251mb | 127.300μs | 135.350μs | 129.379μs | ±1.72% |
| SymfonyDocBlockBench    | 2.163mb | 153.000μs | 161.800μs | 155.170μs | ±1.39% |

### Normalization + Cache

| benchmark               | memory  | min       | max       | mode      | rstdev |
|-------------------------|---------|-----------|-----------|-----------|--------|
| TypeLangAttributesBench | 1.447mb | 65.850μs  | 94.650μs  | 91.945μs  | ±6.51% |
| TypeLangDocBlockBench   | 1.544mb | 91.950μs  | 97.250μs  | 93.070μs  | ±1.49% |
| JMSAttributesBench      | 1.429mb | 88.150μs  | 105.600μs | 100.956μs | ±3.31% |
| SymfonyPHPStanBench     | 1.370mb | 136.050μs | 147.900μs | 138.879μs | ±1.96% |
| ValinorBench            | 1.256mb | 114.450μs | 161.600μs | 152.558μs | ±5.88% |
| SymfonyDocBlockBench    | 2.163mb | 164.300μs | 221.300μs | 212.265μs | ±5.18% |
