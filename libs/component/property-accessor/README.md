# Property Accessor

Reads and writes one property of any object of its class at the access
point given by its metadata: the backing store, a hook or a method.
Strategies of the user are tried before the default ones.

```php
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;

$class = new \ReflectionClass(User::class);
$factory = PropertyAccessorFactory::createDefault([new LazyAccessStrategy()]);

$factory->createWriter($class, $metadata)->write($user, 'user@example.com');

echo $factory->createReader($class, $metadata)->read($user); // user@example.com
```
