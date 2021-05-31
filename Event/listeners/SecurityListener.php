<?php


namespace Sirius\Event\listeners;


use Sirius\Event\Dispatcher;
use Sirius\Event\Events\RequestEvent;
use Sirius\security\Firewall;
use Sirius\security\Security;

class SecurityListener
{
    public Security $security;

    public function __construct()
    {
        $this->security = new Security();
    }

    public function setListener(string $eventName, $method, Dispatcher $dispatcher)
    {
        $listenerData[] = $this;
        $listenerData[] = $method;
        $dispatcher->addListener($listenerData, $eventName);
    }

    public function onRequest(RequestEvent $event)
    {
        $kernel = $event->getKernel();
        $this->security->verifyLoggedUser($kernel);
    }
}