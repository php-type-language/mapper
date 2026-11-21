# Mapping

Mapping of classes: attributes, metadata model and its reading from configs, attributes, phpdoc and reflection.

```php
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

$provider = MetadataBuilder::createDefault()
    ->withYamlReader(__DIR__ . '/mapping.yaml', 400)
    ->withNamingStrategy(new SnakeCaseNamingStrategy())
    ->build();

$metadata = $provider->read(new \ReflectionClass(App\Model\User::class));
```
