<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use TypeLang\Mapper\Bridge\Symfony\Request\Exception\InvalidRequestException;
use TypeLang\Mapper\Bridge\Symfony\Request\ProblemResponseListener;
use TypeLang\Mapper\Bridge\Symfony\Tests\TestCase;
use TypeLang\Mapper\Exception\InvalidValueException;

final class ProblemResponseListenerTest extends TestCase
{
    public function testInvalidRequestIsAnsweredWithProblem(): void
    {
        $exception = InvalidRequestException::becauseDataIsNotMapped(
            InvalidValueException::becauseInvalidType('int', 'old'),
        );

        $event = self::dispatch($exception);
        $response = $event->getResponse();

        self::assertNotNull($response);
        self::assertSame(422, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->headers->get('Content-Type'));

        $body = \json_decode((string) $response->getContent(), true);

        self::assertIsArray($body);
        self::assertSame(422, $body['status']);
        self::assertSame($exception->violations, $body['violations']);
    }

    public function testOtherExceptionIsLeftToFramework(): void
    {
        self::assertNull(self::dispatch(new \RuntimeException())->getResponse());
    }

    private static function dispatch(\Throwable $exception): ExceptionEvent
    {
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): never
            {
                throw new \LogicException('Not called');
            }
        };

        $event = new ExceptionEvent($kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, $exception);

        (new ProblemResponseListener())->onKernelException($event);

        return $event;
    }
}
