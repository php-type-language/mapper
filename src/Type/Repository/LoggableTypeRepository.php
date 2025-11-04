<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Type\Repository\TypeDecorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeRepository extends TypeRepositoryDecorator
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_REPOSITORY_GROUP_NAME = 'REPOSITORY';

    public function __construct(
        private readonly LoggerInterface $logger,
        TypeRepositoryInterface $delegate,
        /**
         * @var non-empty-string
         */
        private readonly string $group = self::DEFAULT_REPOSITORY_GROUP_NAME,
    ) {
        parent::__construct($delegate);
    }

    /**
     * @return non-empty-string
     */
    private function getInstanceName(object $entry): string
    {
        return $entry::class . '#' . \spl_object_id($entry);
    }

    private function logBefore(TypeStatement $statement): void
    {
        $this->logger->debug('[{group}] Fetching type by {statement_name} statement', [
            'group' => $this->group,
            'statement_name' => $this->getInstanceName($statement),
            'statement' => $statement,
        ]);
    }

    private function logAfter(TypeStatement $statement, TypeInterface $type): void
    {
        $unwrapped = $this->unwrap($type);

        $this->logger->info('[{group}] Fetched {type_name} type', [
            'group' => $this->group,
            'statement_name' => $this->getInstanceName($statement),
            'type_name' => $this->getInstanceName($unwrapped),
            'statement' => $statement,
            'type' => $unwrapped,
        ]);
    }

    private function logError(TypeStatement $statement, \Throwable $e): void
    {
        $this->logger->error('[{group}] Fetch error: {error}', [
            'group' => $this->group,
            'statement_name' => $this->getInstanceName($statement),
            'statement' => $statement,
            'error' => $e->getMessage(),
        ]);
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        $this->logBefore($statement);

        try {
            $type = parent::getTypeByStatement($statement);
        } catch (\Throwable $e) {
            $this->logError($statement, $e);

            throw $e;
        }

        $this->logAfter($statement, $type);

        return $type;
    }

    /**
     * @template TArgResult of mixed
     *
     * @param TypeInterface<TArgResult> $type
     *
     * @return TypeInterface<TArgResult>
     */
    private function unwrap(TypeInterface $type): TypeInterface
    {
        if ($type instanceof TypeDecoratorInterface) {
            /** @var TypeInterface<TArgResult> */
            return $type->getDecoratedType();
        }

        return $type;
    }
}
