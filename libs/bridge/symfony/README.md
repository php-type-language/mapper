# Symfony

Registers the mapper in a Symfony application: a default mapper and named
mappers extending its settings.

```yaml
type_lang_mapper:
  naming: snake_case
  types: [App\Mapper\UuidTypeBuilder]
  mappers:
    api:
      strict: true
      types: [App\Mapper\MoneyTypeBuilder]
```

The default mapper is `type_lang_mapper.mapper`, a named one is
`type_lang_mapper.mapper.api` (autowired as `DenormalizerInterface $apiMapper`).
