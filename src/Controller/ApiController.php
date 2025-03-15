<?php

namespace App\Controller;

use App\Entity\EndpointEvent;
use App\Entity\FileEntity;
use App\Message\FetchDataMessage;
use App\Service\ApiDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DirectoryFileRepository;
use App\Repository\DirectoryRepository;
use App\Repository\FileEntityRepository;
use App\Service\EndpointEventLogger;
use Symfony\Component\Messenger\MessageBusInterface;

final class ApiController extends AbstractController
{
    private $repository;
    private $fileRepository;
    private $directoryRepository;
    private $eventLogger;

    // Injecting RequestStack and EntityManager into the controller
    public function __construct(
        DirectoryFileRepository $repository,
        FileEntityRepository $fileRepository,
        DirectoryRepository $directoryRepository,
        EndpointEventLogger $eventLogger
        )
    {
        $this->repository = $repository;
        $this->fileRepository = $fileRepository;
        $this->directoryRepository = $directoryRepository;
        $this->eventLogger = $eventLogger; 
    }

    #[Route('/api/fetch-data', name: 'api_fetch_data', methods: ['GET'])]
    public function fetchDataAndStore(MessageBusInterface $bus): JsonResponse
    {
        $this->eventLogger->logEvent('fetch_data', 'started', 'Fetching data from external API');
        try {
            $bus->dispatch(new FetchDataMessage());
            $this->eventLogger->logEvent('fetch_data', 'success', 'Data fetched and stored successfully');
        } catch (\Exception $e) {
            $this->eventLogger->logEvent('fetch_data', 'failure', $e->getMessage());
        }

        return new JsonResponse(['message' => 'Error fetching data from external API'], 500);
    }

    #[Route('/api/files-and-directories', name: 'api_files_and_directories')]
    public function filesAndDirectories(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 100);
        try {
            $data = $this->repository->findByUrlPagginated($limit, $page*$limit);
            $this->eventLogger->logEvent('filesAndDirectories', 'success', 'Data fetched and stored successfully');

        } catch (\Exception $e) {
            $this->eventLogger->logEvent('fetch_data', 'failure', $e->getMessage());        
        }
        
        return new JsonResponse($data);

    }
    
    #[Route('/api/directories', name: 'api_directories')]
    public function directories(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 100);
        
        try {
            $data = $this->directoryRepository->findByUrlPagginated($limit, $page*$limit);
            $this->eventLogger->logEvent('directories', 'success', 'Data fetched and stored successfully');
        } catch (\Exception $e) {
            $this->eventLogger->logEvent('fetch_data', 'failure', $e->getMessage());        
        }
        
        return new JsonResponse($data);
    }

    #[Route('/api/files', name: 'api_files')]
    public function files(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 100);
        
        try{
            $data = $this->fileRepository->findByUrlPagginated($limit, $page*$limit);
            $this->eventLogger->logEvent('files', 'success', 'Data fetched and stored successfully');   
        } catch (\Exception $e) {
            $this->eventLogger->logEvent('fetch_data', 'failure', $e->getMessage());        
        }

        return new JsonResponse($data);
    }
}
