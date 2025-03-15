<?php

namespace App\Controller;

use Dba\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DatabaseTestController extends AbstractController
{

    #[Route('/test-db', name: 'test_db')]
    public function testDatabaseConnection(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $connection = $entityManager->getConnection();
            $connection->connect();

            if ($connection->isConnected()) {
                return new JsonResponse(['status' => 'success', 'message' => 'Database connected successfully']);
            } else {
                return new JsonResponse(['status' => 'error', 'message' => 'Database connection failed'], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/tables', name: 'api_tables')]
    public function getDbTables(EntityManagerInterface $entityManager): JsonResponse
    {
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        
        // Get all table names in the current schema
        $tables = $connection->createSchemaManager()->listTableNames();

        return new JsonResponse($tables);
    }
}
