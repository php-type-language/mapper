<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\Builder\TypeAliasBuilder\Reason;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

/**
 * @template-covariant TType of TypeInterface = TypeInterface<mixed>
 * @template-extends NamedTypeBuilder<TType>
 */
class TypeAliasBuilder extends NamedTypeBuilder
{
    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $aliases
     */
    public function __construct(
        array|string $aliases,
        /**
         * @var NamedTypeBuilder<TType>
         */
        protected readonly NamedTypeBuilder $delegate,
        /**
         * @internal will be used in the future to notify about the
         *           use of incorrect types
         */
        protected readonly Reason $reason = Reason::DEFAULT,
        protected readonly PrinterInterface $printer = new PrettyPrinter(),
    ) {
        parent::__construct($aliases);
    }

    private function logDeprecation(NamedTypeNode $stmt, ?LoggerInterface $logger): void
    {
        if ($logger === null) {
            return;
        }

        $logger->warning('Deprecated type "{type}" usage', [
            'type' => $this->printer->print($stmt),
            'stmt' => $stmt,
        ]);
    }

    private function logNonCanonical(NamedTypeNode $stmt, ?LoggerInterface $logger): void
    {
        if ($logger === null) {
            return;
        }

        $logger->warning('Non-canonical type name "{type}" usage', [
            'type' => $this->printer->print($stmt),
            'stmt' => $stmt,
        ]);
    }

    private function logTemporary(NamedTypeNode $stmt, ?LoggerInterface $logger): void
    {
        if ($logger === null) {
            return;
        }

        $logger->notice('Type "{type}" may change semantics in future', [
            'type' => $this->printer->print($stmt),
            'stmt' => $stmt,
        ]);
    }

    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface
    {
        switch ($this->reason) {
            case Reason::Deprecated:
                $this->logDeprecation($stmt, $context->config->findLogger());
                break;

            case Reason::NonCanonical:
                $this->logNonCanonical($stmt, $context->config->findLogger());
                break;

            case Reason::Temporary:
                $this->logTemporary($stmt, $context->config->findLogger());
                break;
        }

        return $this->delegate->build($stmt, $context);
    }
}
