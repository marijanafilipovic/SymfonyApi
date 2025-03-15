<?php
namespace App\MessageHandler;

use App\Message\FetchDataMessage;
use App\Service\ApiDataService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FetchDataHandler
{
    private ApiDataService $apiDataService;

    public function __construct(ApiDataService $apiDataService)
    {
        $this->apiDataService = $apiDataService;
    }

    public function __invoke(FetchDataMessage $message)
    {
        die(var_dump($this->apiDataService->fetchDataAndStore()));
    }
}
