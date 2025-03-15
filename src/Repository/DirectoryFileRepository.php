<?php

namespace App\Repository;

use App\Entity\DirectoryEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DirectoryEntity>
 */
class DirectoryFileRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, DirectoryEntity::class);
        $this->em = $entityManager;
    }

    public function findByUrlPagginated(int $limit = 100, int $offset = 0): array
    {
        $conn = $this->em->getConnection();

        $sql = "
        SELECT
            d.id AS directory_id, 
            d.name AS directory_name, 
            d.url AS directory_url,
            f.id AS file_id, 
            f.name AS file_name, 
            f.path AS file_path,  
            f.url AS file_url
        FROM directory_entity d
        JOIN file_entity f ON d.name = f.path
        AND d.url = f.url
        LIMIT :limit OFFSET :offset
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
   
        $resultSet = $stmt->executeQuery();

        //  die(var_dump($resultSet->fetchAllAssociative()));

        return $this->refactorDataStructure($resultSet->fetchAllAssociative());
    }

    public function refactorDataStructure(array $data): array
    {
        $refactoredData = [];
    
        foreach ($data as $item) {
            $ipAddress = $item['directory_url']; // Use URL as the top-level key
            $directoryPath = explode('/', trim($item['directory_name'], '/'));
    
            if (!isset($refactoredData[$ipAddress])) {
                $refactoredData[$ipAddress] = [];
            }
    
            $currentLevel = &$refactoredData[$ipAddress];
    
            foreach ($directoryPath as $pathPart) {
                if (!isset($currentLevel[$pathPart])) {
                    $currentLevel[$pathPart] = [];
                }
                $currentLevel = &$currentLevel[$pathPart]; // Move deeper
            }
    
            // Add files at the deepest directory level
            if (!isset($currentLevel['files'])) {
                $currentLevel['files'] = [];
            }
            $currentLevel['files'][] = $item['file_name'];
        }
    
        return $refactoredData;
    }
}
