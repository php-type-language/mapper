# Property Accessor

Reads and writes one property of any object of its class, regardless of its
visibility, the way of access is chosen once by the declaration of the
property and its metadata. Strategies of the user are tried before the
default ones.

```php
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;

$property = new \ReflectionProperty(User::class, 'email');
$factory = new PropertyAccessorFactory([new GetterSetterStrategy()]);

$factory->createWriter($property, $metadata)->write($user, 'user@example.com');

echo $factory->createReader($property, $metadata)->read($user); // user@example.com
```
