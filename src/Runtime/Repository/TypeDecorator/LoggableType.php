<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\TypeDecorator;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
final class LoggableType extends TypeDecorator
{
    public function __construct(
        private readonly LoggerInterface $logger,
        TypeInterface $delegate,
    ) {
        parent::__construct($delegate);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function getLoggerArguments(mixed $value, Context $context): array
    {
        $path = $context->getPath();
        $delegate = $this->getDecoratedType();

        return [
            'value' => $value,
            'type' => $delegate,
            'type_name' => $delegate::class . '#' . \spl_object_id($delegate),
            'path' => $path->toArray(),
        ];
    }

    public function match(mixed $value, Context $context): bool
    {
        $this->logger->debug(
            'Matching by the {type_name}',
            $this->getLoggerArguments($value, $context),
        );

        $result = parent::match($value, $context);

        $this->logger->info(
            $result === true
                ? 'Matched by the {type_name}'
                : 'Not matched by the {type_name}',
            $this->getLoggerArguments($value, $context),
        );

        return $result;
    }

    public function cast(mixed $value, Context $context): mixed
    {
        $this->logger->debug(
            'Casting by the {type_name}',
            $this->getLoggerArguments($value, $context),
        );

        try {
            $result = parent::cast($value, $context);
        } catch (\Throwable $e) {
            $this->logger->error('Casting by the {type_name} was failed', [
                ...$this->getLoggerArguments($value, $context),
                'error' => $e,
            ]);
            throw $e;
        }

        $this->logger->info('Casted by the {type_name}', [
            ...$this->getLoggerArguments($value, $context),
            'result' => $result,
        ]);

        return $result;
    }
}
