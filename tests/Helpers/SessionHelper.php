<?php

namespace App\Tests\Helpers;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

trait SessionHelper
{
    public function createSession(KernelBrowser $client): Session
    {
        $container = $client->getContainer();
        $sessionSavePath = $container->getParameter('session.save_path');
        $sessionStorage = new MockFileSessionStorage($sessionSavePath);

        $session = new Session($sessionStorage);
        $session->start();
        $session->save();

        $sessionCookie = new Cookie(
            $session->getName(),
            $session->getId(),
            null,
            null,
            'localhost',
        );
        $client->getCookieJar()->set($sessionCookie);

        return $session;
    }
}