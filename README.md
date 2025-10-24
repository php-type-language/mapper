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

| benchmark                                 | memory      | min       | mode       | rstdev |
|-------------------------------------------|-------------|-----------|------------|--------|
| JMSAttributesBench                        | 853.248kb   | 41.890μs  | 42.562μs   | ±6.53% |
| JMSAttributesWithSymfonyPsr6Bench         | 718.816kb   | 42.500μs  | 42.926μs   | ±0.46% |
| TypeLangAttributesWithSymfonyPsr16Bench   | 721.720kb   | 52.590μs  | 53.200μs   | ±0.56% |
| TypeLangDocBlockBench                     | 839.008kb   | 52.490μs  | 53.155μs   | ±6.83% |
| TypeLangDocBlockWithSymfonyPsr16Bench     | 786.512kb   | 52.500μs  | 52.976μs   | ±2.31% |
| TypeLangAttributesWithSymfonyPsr6Bench    | 717.960kb   | 52.840μs  | 53.031μs   | ±0.87% |
| TypeLangDocBlockWithSymfonyPsr6Bench      | 783.312kb   | 52.940μs  | 53.203μs   | ±0.24% |
| TypeLangAttributesBench                   | 1.209mb     | 53.160μs  | 53.459μs   | ±1.55% |
| SymfonyPHPStanBench                       | 710.344kb   | 140.460μs | 141.594μs  | ±0.64% |
| SymfonyDocBlockBench                      | 1.850mb     | 140.910μs | 142.580μs  | ±0.89% |
| ValinorBench                              | 894.984kb   | 142.120μs | 142.259μs  | ±0.44% |
| ValinorWithCustomPsr16Bench               | 574.496kb   | 143.380μs | 144.214μs  | ±0.73% |
| ValinorWithSymfonyPsr16Bench              | 548.008kb   | 145.680μs | 151.617μs  | ±1.83% |
| SymfonyDocBlockWithSymfonyPsr6Bench       | 644.608kb   | 200.810μs | 203.024μs  | ±1.48% |
| SymfonyPHPStanWithSymfonyPsr6Bench        | 565.376kb   | 202.000μs | 202.599μs  | ±1.21% |

### Normalization

Normalization: For example, conversion from PHP Object to JSON.

| benchmark                               | memory      | min      | mode      | rstdev    |
|-----------------------------------------|-------------|----------|-----------|-----------|
| ValinorWithCustomPsr16Bench             | 885.496kb   | 10.060μs | 10.107μs  | ±0.79%    |
| ValinorBench                            | 866.440kb   | 38.250μs | 38.419μs  | ±2.14%    |
| SymfonyPHPStanBench                     | 647.632kb   | 41.220μs | 43.365μs  | ±2.17%    |
| SymfonyDocBlockBench                    | 1.850mb     | 43.120μs | 43.370μs  | ±1.11%    |
| TypeLangAttributesWithSymfonyPsr16Bench | 721.720kb   | 50.520μs | 50.844μs  | ±2.82%    |
| TypeLangAttributesWithSymfonyPsr6Bench  | 717.960kb   | 50.590μs | 50.952μs  | ±1.46%    |
| TypeLangAttributesBench                 | 1.200mb     | 50.610μs | 51.502μs  | ±1.25%    |
| TypeLangDocBlockWithSymfonyPsr6Bench    | 783.256kb   | 50.800μs | 50.859μs  | ±0.37%    |
| TypeLangDocBlockBench                   | 838.272kb   | 50.810μs | 51.446μs  | ±0.60%    |
| TypeLangDocBlockWithSymfonyPsr16Bench   | 786.456kb   | 51.960μs | 52.670μs  | ±20.25%   |
| JMSAttributesBench                      | 927.920kb   | 56.070μs | 56.750μs  | ±2.30%    |
| JMSAttributesWithSymfonyPsr6Bench       | 793.808kb   | 56.170μs | 57.160μs  | ±2.00%    |
| SymfonyDocBlockWithSymfonyPsr6Bench     | 603.352kb   | 77.490μs | 78.519μs  | ±1.21%    |
| SymfonyPHPStanWithSymfonyPsr6Bench      | 448.344kb   | 78.220μs | 79.170μs  | ±1.93%    |
| ValinorWithSymfonyPsr16Bench*           | 436.760kb   | ERROR    | ERROR     | ±31.62%   |

- `ValinorWithSymfonyPsr16Bench` - cuyz/valinor does not support PSR-16 cache 
  (not compatible with other implementations): https://github.com/CuyZ/Valinor/issues/623
  >  Uncaught Error: Object of type CuyZ\Valinor\Normalizer\Transformer\EvaluatedTransformer is not callable
