<?php

namespace App\Chore\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ExceptionSubscriber is a subscriber that handles exception related events.
 * It implements the EventSubscriberInterface to use Symfony's event subscriber functionalities.
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Handle kernel exception events.
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if($throwable instanceof HttpExceptionInterface) {
            /** @var HttpExceptionInterface $throwable */
            $response_code = $throwable->getStatusCode();

            // Redirect to the corresponding error page for the following status codes.
            foreach(['403', '404', '500', '503'] as $code) {
                if($response_code == $code) {
                    $response = new RedirectResponse('/'.$code);
                    $event->setResponse($response);
                }
            }
        }
    }

    /**
     * Get the events this subscriber is interested in.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
