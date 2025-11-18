<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Instantiator;

use Doctrine\Instantiator\Instantiator as DoctrineInstantiator;

final class DoctrineClassInstantiator implements ClassInstantiatorInterface
{
    public function __construct(
        private readonly DoctrineInstantiator $instantiator = new DoctrineInstantiator(),
    ) {}

    public static function isSupported(): bool
    {
        return \class_exists(DoctrineInstantiator::class);
    }

    public function instantiate(string $class): object
    {
        return $this->instantiator->instantiate($class);
    }
}
