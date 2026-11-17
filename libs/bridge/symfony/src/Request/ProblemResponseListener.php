<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use TypeLang\Mapper\Bridge\Symfony\Request\Exception\InvalidRequestException;

/**
 * Answers {@see InvalidRequestException} with an RFC 9457 problem in JSON
 * listing the violations, other exceptions are left to the framework.
 *
 * @link https://www.rfc-editor.org/rfc/rfc9457
 */
final class ProblemResponseListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof InvalidRequestException) {
            return;
        }

        $status = $exception->getStatusCode();

        $event->setResponse(new JsonResponse(
            data: [
                'type' => 'about:blank',
                'title' => 'Unprocessable Content',
                'status' => $status,
                'detail' => $exception->getMessage(),
                'violations' => $exception->violations,
            ],
            status: $status,
            headers: ['Content-Type' => 'application/problem+json'],
        ));
    }
}
