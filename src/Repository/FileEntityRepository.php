<?php

namespace App\Repository;

use App\Entity\FileEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileEntity>
 */
class FileEntityRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, FileEntity::class);
        $this->em = $entityManager;
    }

    public function findByUrlPagginated(int $limit = 3000, int $offset = 0): array
    {
        $conn = $this->em->getConnection();

        $sql = "
        SELECT 
            f.name AS file_name
        FROM file_entity f
        LIMIT :limit OFFSET :offset
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
   
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }
}