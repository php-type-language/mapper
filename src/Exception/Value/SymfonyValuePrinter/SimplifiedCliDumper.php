<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Value\SymfonyValuePrinter;

use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Exception\Value
 */
final class SimplifiedCliDumper extends CliDumper
{
    public function __construct($output = null, ?string $charset = null, int $flags = 0)
    {
        parent::__construct($output, $charset, $flags);

        $this->setColors(false);
    }

    public function dump(Data $data, $output = null): ?string
    {
        $result = parent::dump($data, $output);

        if ($result !== null) {
            return \rtrim($result, "\n");
        }

        return null;
    }
}
