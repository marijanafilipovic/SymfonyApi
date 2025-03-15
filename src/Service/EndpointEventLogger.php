<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EndpointEvent;

class EndpointEventLogger
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function logEvent(string $eventName, string $status, string $message = '')
    {
        $event = new EndpointEvent();
        $event->setFunctionName($eventName);
        $event->setStatus($status);
        $event->setMessage($message);
        $event->setTimestamp(new \DateTime());

        // $this->entityManager->persist($event);
        $this->entityManager->flush();
    }
}
