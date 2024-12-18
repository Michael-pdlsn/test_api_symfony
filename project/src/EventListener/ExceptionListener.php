<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = 500;

        if ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $message = 'Full authentication is required to access this resource.';
        } elseif ($exception instanceof AccessDeniedException) {
            $statusCode = 403;
            $message = 'Access Denied. You do not have the necessary permissions.';
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } else {
            $message = $exception->getMessage() ?? 'An unexpected error occurred.';
        }

        $response = new JsonResponse([
            'error' => JsonResponse::$statusTexts[$statusCode] ?? 'Error',
            'message' => $message,
        ], $statusCode);

        $event->setResponse($response);
    }
}
