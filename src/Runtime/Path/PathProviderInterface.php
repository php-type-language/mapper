<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Path;

interface PathProviderInterface
{
    public function getPath(): PathInterface;
}
