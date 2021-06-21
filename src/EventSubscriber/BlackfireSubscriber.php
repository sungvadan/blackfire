<?php

namespace App\EventSubscriber;

use Blackfire\Client;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class BlackfireSubscriber implements EventSubscriberInterface
{
    /**
     * @var Probe|null
     */
    private $probe;
    public function onRequestEvent(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $shouldProfile = $request->getPathInfo() === '/api/github-organization';
        $shouldProfile =  false;
        if ($shouldProfile) {
            $blackfire = new Client();
            $configuration = new Configuration();
            $configuration->setTitle('Profil automatique github request');
            $this->probe = $blackfire->createProbe($configuration);
        }
    }

    public function onTerminateEvent(TerminateEvent $event)
    {
        if ($this->probe) {
            $this->probe->close();
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => ['onRequestEvent', 1000],
            TerminateEvent::class => 'onTerminateEvent'
        ];
    }
}
