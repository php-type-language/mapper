<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class PHPConfigDriver extends ArrayConfigDriver
{
    /**
     * @param array<class-string, array<array-key, mixed>> $config
     */
    public function __construct(
        private readonly array $config,
        DriverInterface $delegate = new NullDriver(),
        ?ExpressionLanguage $expression = null,
    ) {
        parent::__construct($delegate, $expression);
    }

    protected function getConfiguration(\ReflectionClass $class): ?array
    {
        return $this->config[$class->name] ?? null;
    }
}
