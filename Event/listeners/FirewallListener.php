<?php


namespace Sirius\Event\listeners;


use Sirius\Event\Dispatcher;
use Sirius\Event\Events\RequestEvent;
use Sirius\security\Firewall;

class FirewallListener
{
    /**
     * @var Dispatcher
     */
    public Dispatcher $dispatcher;

    /**
     * @var Firewall
     */
    public Firewall $firewall;

    public function __construct()
    {
        $this->firewall = new Firewall();
    }

    public function setListener(string $eventName, $method, Dispatcher $dispatcher)
    {
        $listenerData[] = $this;
        $listenerData[] = $method;
        $dispatcher->addListener($listenerData, $eventName);
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $this->firewall->checkFirewalls($request);

    }
}