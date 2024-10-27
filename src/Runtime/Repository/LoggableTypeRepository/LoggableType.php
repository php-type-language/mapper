<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\LoggableTypeRepository;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
final class LoggableType implements TypeInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeInterface $delegate,
    ) {}

    /**
     * @return array<array-key, mixed>
     */
    private function getLoggerArguments(mixed $value, Context $context): array
    {
        $path = $context->getPath();

        return [
            'value' => $value,
            'type' => $this->delegate,
            'type_name' => $this->delegate::class . '#' . \spl_object_id($this->delegate),
            'path' => $path->toArray(),
        ];
    }

    public function match(mixed $value, Context $context): bool
    {
        $this->logger->debug(
            'Matching the value by the type {type_name}',
            $this->getLoggerArguments($value, $context),
        );

        $result = $this->delegate->match($value, $context);

        $this->logger->debug(
            $result === true
                ? '[matched] The value will be matched by the type {type_name}'
                : '[not-matched] The value will NOT be matched by the type {type_name}',
            $this->getLoggerArguments($value, $context),
        );

        return $result;
    }

    public function cast(mixed $value, Context $context): mixed
    {
        $this->logger->debug(
            'Casting the value using type {type_name}',
            $this->getLoggerArguments($value, $context),
        );

        try {
            $result = $this->delegate->cast($value, $context);
        } catch (\Throwable $e) {
            $this->logger->error('Casting of the value using type {type_name} was failed', [
                ...$this->getLoggerArguments($value, $context),
                'error' => $e,
            ]);
            throw $e;
        }

        $this->logger->debug('The value will be casted by the type {type_name}', [
            ...$this->getLoggerArguments($value, $context),
            'result' => $result,
        ]);

        return $result;
    }
}
