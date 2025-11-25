<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\Symfony;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use TypeLang\Mapper\Extension\Symfony\DependencyInjection\TypeLangExtension;

final class TypeLangMapperBundle extends AbstractBundle
{
    public function getContainerExtension(): TypeLangExtension
    {
        return new TypeLangExtension();
    }
}
