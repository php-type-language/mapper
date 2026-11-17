# Type Matcher

Matches a named type against the simple forms it is allowed to be written in
and names its template arguments.

```php
use TypeLang\Mapper\TypeMatcher\NamedTypePattern;

$pattern = new NamedTypePattern(['int', 'int<min, max>']);

$arguments = $pattern->findArguments($parser->parse('int<0, 42>'));

echo $arguments['max']->value;
// 42
```
