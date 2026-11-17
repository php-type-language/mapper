# Diagnostics

Describes data errors of mapping: where in the input and the type, what code, what text.

```php
use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;

$path = new Path([new PropertySegment('items'), new KeySegment(3)]);

echo (new JsonPathPrinter())->print($path);
// $.items[3]
```
