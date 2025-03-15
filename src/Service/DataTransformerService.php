<?php
namespace App\Service;

use App\Entity\DirectoryEntity;
use App\Entity\FileEntity;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Psr\Log\LoggerInterface;

class DataTransformerService
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function transformAndStore(array $rawData)
    {
        $directories = [];
        $batchSize = 100; // Process in batches of 100 items to avoid memory overload
        $i = 0;
    
        // Store directories first
        foreach ($rawData['items'] as $item) {
            $fileUrl = $item['fileUrl'];
            $urlParts = parse_url($fileUrl);
            $path = $urlParts['path'];
            $baseUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
            $isDirectory = substr($path, -1) === '/';
    
            if ($isDirectory) {
                if (!isset($directories[$path])) {
                    try {
                        $directory = new DirectoryEntity();
                        $directory->setName($path);
                        $directory->setUrl($baseUrl);
                        $this->em->persist($directory);
                        $directories[$path] = $directory;
                        $this->em->flush();
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                }
            }
    
            // Flush and clear after batchSize directories
            if (++$i % $batchSize === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
    
        // Flush remaining directories
        $this->em->flush();
        $this->em->clear();
    
        // Now store files
        $i = 0;
        foreach ($rawData['items'] as $item) {
            $fileUrl = $item['fileUrl'];
            $urlParts = parse_url($fileUrl);
            $path = $urlParts['path'];
            $fileName = basename($path);
            $baseUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
            $isDirectory = substr($path, -1) === '/';
    
            if (!$isDirectory) {
                $file = new FileEntity();
                $file->setName($fileName);
                $file->setPath(str_replace($fileName, '', $path));
                $file->setUrl($baseUrl);
                $this->em->persist($file);
                $this->em->flush();
            }
    
            // Flush the files in batches
            if (++$i % $batchSize === 0) {
                $this->em->flush(); 
                $this->em->clear();
            }
        }
    
        // Final flush for remaining files
        $this->em->flush();
        $this->em->clear();
    }
    


    // public function transformAndStore(array $rawData)
    // {
    //     $directories = [];
    //     foreach ($rawData['items'] as $item) {
    //         $fileUrl = $item['fileUrl'];
    //         $urlParts = parse_url($fileUrl);
    //         $path = $urlParts['path'];
    //         $fileName = basename($path);
    //         $baseUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
    //         $isDirectory = substr($path, -1) === '/';
    //         if ($isDirectory) {
    //             if (!isset($directories[$path])) {
    //                 try {
    //                     $directory = new DirectoryEntity();
    //                     $directory->setName($path);
    //                     $directory->setUrl($baseUrl);
    //                     $this->em->persist($directory);
    //                     $this->em->flush();
    //                } catch (\Throwable $th) {
    //                 throw $th;
    //                }
    //                 $directories[$path] = $directory;
    //             }
    //         } else {
    //                 $file = new FileEntity();
    //                 $file->setName($fileName);
    //                 $file->setPath(str_replace($fileName, '', $path));
    //                 $file->setUrl($baseUrl);
    //                 $this->em->persist($file);
    //                 $this->em->flush();
    //         }
    //     }

    // }


}