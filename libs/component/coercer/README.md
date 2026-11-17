# Coercer

Converts a value to the target type when it is the same value written in
another form, such as `'42'` for `int`.

```php
use TypeLang\Mapper\Coercer\IntCoercer;

$coercer = new IntCoercer();

$coercer->coerce('42');    // 42
$coercer->coerce(42.0);    // 42
$coercer->coerce('42abc'); // null
```
