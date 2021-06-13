<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    public function listTopics(int $page, int $pageSize, string $orderBy, string $orderDirection): array
    {
        $offset = $page * $pageSize;

        return $this->createQueryBuilder('t')
            ->orderBy('t.'. $orderBy, $orderDirection)
            ->setMaxResults($pageSize)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();
    }
}
