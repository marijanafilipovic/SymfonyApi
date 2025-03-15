<?php
namespace App\Repository;

use App\Entity\DirectoryEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DirectoryEntity>
 */
class DirectoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, DirectoryEntity::class);
        $this->em = $entityManager;
    }

    public function findByUrlPagginated(int $limit = 3000, int $offset = 0): array
    {
        $conn = $this->em->getConnection();

        $sql = "
        SELECT
            d.id AS directory_id, 
            d.name AS directory_name, 
            d.url AS directory_url
        FROM directory_entity d
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
        }
    
        return $refactoredData;
    }
}