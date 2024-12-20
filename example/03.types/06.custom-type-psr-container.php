<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Platform\DelegatePlatform;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\Builder\CallableTypeBuilder;
use TypeLang\Mapper\Type\Builder\PsrContainerTypeBuilder;
use TypeLang\Mapper\Type\TypeInterface;

require __DIR__ . '/../../vendor/autoload.php';


class Container implements ContainerInterface
{
    /**
     * @var array<non-empty-string, object>
     */
    private array $services;

    public function __construct()
    {
        $this->services = [
            MyNonEmptyStringType::class => new MyNonEmptyStringType()
        ];
    }

    public function get(string $id)
    {
        return $this->services[$id] ?? throw new \RuntimeException("Service $id not found");
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}


// Add new type (must implement TypeInterface)
class MyNonEmptyStringType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value) && $value !== '';
    }

    public function cast(mixed $value, Context $context): string
    {
        if (\is_string($value) && $value !== '') {
            return $value;
        }

        throw new InvalidValueException(
            value: $value,
            path: $context->getPath(),
            template: 'Passed value cannot be empty, but {{value}} given',
        );
    }
}

$container = new Container();

$mapper = new Mapper(new DelegatePlatform(
    // Extend existing platform (StandardPlatform)
    delegate: new StandardPlatform(),
    types: [
        // Additional type
        new PsrContainerTypeBuilder('custom-string', MyNonEmptyStringType::class, $container),
    ],
));

$result = $mapper->normalize(['example', ''], 'list<custom-string>');

var_dump($result);
//
// expected exception:
//   TypeLang\Mapper\Exception\Mapping\InvalidIterableValueException:
//     Passed value "" on index 1 in ["example", ""] is invalid at $[1]
//
// previous exception:
//   TypeLang\Mapper\Exception\Mapping\InvalidValueException:
//     Passed value cannot be empty, but "" given at $[1]
//
