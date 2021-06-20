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

    public function listTopics(int $page, int $pageSize, string $orderBy, string $orderDirection, string $text, string $tags): array
    {
        $offset = $page * $pageSize;
        $qb = $this->createQueryBuilder('t');
        return $qb
            ->andWhere($qb->expr()->like('t.title', ':text'))
            ->andWhere($qb->expr()->like('t.tags', ':tags'))
            ->setParameter('text', '%' . $text . '%')
            ->setParameter('tags', '%' . $tags . '%')
            ->orderBy('t.' . $orderBy, $orderDirection)
            ->addOrderBy('t.id', 'asc')
            ->setMaxResults($pageSize)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();
    }
}
