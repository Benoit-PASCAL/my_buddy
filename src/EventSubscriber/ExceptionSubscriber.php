<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if($throwable instanceof HttpExceptionInterface) {
            /** @var HttpExceptionInterface $throwable */
            $response_code = $throwable->getStatusCode();

            foreach(['403', '404', '500', '503'] as $code) {
                if($response_code == $code) {
                    $response = new RedirectResponse('/'.$code);
                    $event->setResponse($response);
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
