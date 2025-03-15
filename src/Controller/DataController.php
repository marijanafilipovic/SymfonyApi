<?php
namespace App\Controller;

use App\Message\FetchDataMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataController
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function fetchDataAction()
    {
        $this->bus->dispatch(new FetchDataMessage('http://external-api.com/data'));

        return new JsonResponse(['message' => 'Data processing has started.'], 202);
    }
}