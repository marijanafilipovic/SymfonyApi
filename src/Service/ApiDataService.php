<?php
namespace App\Service;


use App\Service\DataTransformerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiDataService
{
    private $client;
    private $em;
    private $logger;
    private $dataTransformer;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em, LoggerInterface $logger, DataTransformerService $dataTransformer)
    {
        $this->client = $client;
        $this->em = $em;
        $this->logger = $logger;
        $this->dataTransformer = $dataTransformer;
    }

    public function fetchDataAndStore()
    {
        // Fetch the data from the external API
        $response = $this->client->request('GET', 'https://rest-test-eight.vercel.app/api/test');
        // var_dump($response);
        $data = $response->toArray();
        // var_dump($data);
        // $data = (array) $response;
        return $this->dataTransformer->transformAndStore($data);

        $this->logger->info('Data fetched from external API at ' . (new \DateTime())->format('Y-m-d H:i:s'));
    }

    private function transformData($data)
    {
        return $data;
        // Implement the transformation logic here
        $transformedData = [];
        // Your logic here...
        return $transformedData;
    }

    private function storeData($rawData)
    {
        // $this->dataTransformer->transformAndStore($rawData);
        // Implement the storage logic here
        // Example:
        // $directory = new Directory();
        // $directory->setName('Some Name');
        // $this->em->persist($directory);
        // $this->em->flush();
    }
}