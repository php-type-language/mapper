<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class PHPConfigFileDriver extends ArrayConfigDriver
{
    /**
     * @param non-empty-string $directory
     */
    public function __construct(
        private readonly string $directory,
        DriverInterface $delegate = new NullDriver(),
        ?ExpressionLanguage $expression = null,
    ) {
        parent::__construct($delegate, $expression);
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-string
     */
    private function getFilename(\ReflectionClass $class): string
    {
        return \str_replace('\\', '.', $class->getName())
            . '.php';
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-string
     */
    private function getPathname(\ReflectionClass $class): string
    {
        return $this->directory . '/' . $this->getFilename($class);
    }

    protected function getConfiguration(\ReflectionClass $class): ?array
    {
        $pathname = $this->getPathname($class);

        if (\is_file($pathname)) {
            \ob_start();
            $result = require $pathname;
            \ob_end_clean();

            if (!\is_array($result)) {
                throw new \InvalidArgumentException(\sprintf(
                    'Configuration file "%s" must contain array, but "%s" given',
                    // @phpstan-ignore-next-line
                    \realpath($pathname) ?: $pathname,
                    \get_debug_type($result),
                ));
            }

            return $result;
        }

        return null;
    }
}
