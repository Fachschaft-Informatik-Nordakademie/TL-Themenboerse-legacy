<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;

class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    public function listTopics(int $page, int $pageSize, string $orderBy, string $orderDirection, $text): array
    {
        $offset = $page * $pageSize;

        return $this->createQueryBuilder('t')
            ->where("t.text like '%:text%'")
            ->setParameter('text', $text)
            ->orderBy('t.' . $orderBy, $orderDirection)
            ->addOrderBy('t.id', 'asc')
            ->setMaxResults($pageSize)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();
    }
}
