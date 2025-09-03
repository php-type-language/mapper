<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Value;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use TypeLang\Mapper\Exception\Environment\ComposerPackageRequiredException;

final class SymfonyValuePrinter implements ValuePrinterInterface
{
    private readonly AbstractDumper $dumper;

    private readonly ClonerInterface $cloner;

    public function __construct(
        ?AbstractDumper $dumper = null,
        ?ClonerInterface $cloner = null,
    ) {
        self::assertKernelPackageIsInstalled();

        $this->dumper = $dumper ?? $this->createDefaultDataDumper();
        $this->cloner = $cloner ?? $this->createDefaultVarCloner();
    }

    private function createDefaultDataDumper(): CliDumper
    {
        $dumper = new class extends CliDumper {
            public function dump(Data $data, $output = null): ?string
            {
                $result = parent::dump($data, $output);

                if ($result !== null) {
                    return \rtrim($result, "\n");
                }

                return null;
            }
        };
        $dumper->setColors(false);

        return $dumper;
    }

    private function createDefaultVarCloner(): VarCloner
    {
        $cloner = new VarCloner();
        /** @phpstan-ignore-next-line Allow internal class usage */
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

        return $cloner;
    }

    /**
     * @throws ComposerPackageRequiredException
     */
    private static function assertKernelPackageIsInstalled(): void
    {
        if (!\interface_exists(DataDumperInterface::class)) {
            throw ComposerPackageRequiredException::becausePackageNotInstalled(
                package: 'symfony/var-dumper',
                purpose: 'Symfony value printer support',
            );
        }
    }

    public function print(mixed $value): string
    {
        $result = $this->cloner->cloneVar($value);

        return (string) $this->dumper->dump($result, true);
    }
}
