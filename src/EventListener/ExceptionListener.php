<?php

namespace App\EventListener;

use App\Exceptions\Interfaces\ApiExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener implements EventSubscriberInterface
{

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ApiExceptionInterface) {
            $response = new JsonResponse();

            $response->setData([
                'error' => $exception->getMessage()
            ]);
            $response->setStatusCode($exception->getCode());

            $event->setResponse($response);
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => 'onKernelException'
        ];
    }
}
