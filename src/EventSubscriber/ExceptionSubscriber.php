<?php

namespace App\EventSubscriber;

use App\Exception\ForbiddenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $data = [
            'status' => $exception->getCode(), // Le status n'existe pas car ce n'est pas une exception HTTP, donc on met 500 par défaut.
            'message' => $exception->getMessage()
        ];

        $event->setResponse(new JsonResponse($data));

        if ($exception instanceof HttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data, $exception->getStatusCode()));
        }

        if ($exception instanceof ForbiddenException) {
            $data = [
                'status' => 403,
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data, JsonResponse::HTTP_FORBIDDEN));
        }

        if ($exception instanceof JWTDecodeFailureException) {
            $data = [
                'status' => 401,
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data, JsonResponse::HTTP_UNAUTHORIZED));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
