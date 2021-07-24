<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function hasCandidateForTopic(int $user, int $topic): bool
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->leftJoin('a.topic', 't')
            ->leftJoin('a.candidate', 'u')
            ->andWhere('t.id = :t_id')
            ->andWhere('u.id = :u_id')
            ->setParameter('t_id', $topic)
            ->setParameter('u_id', $user)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function delete(int $user, int $topic)
    {
        $applicationId = $this->createQueryBuilder('a')
            ->select('a.id')
            ->leftJoin('a.topic', 't')
            ->leftJoin('a.candidate', 'u')
            ->andWhere('t.id = :t_id')
            ->andWhere('u.id = :u_id')
            ->setParameter('t_id', $topic)
            ->setParameter('u_id', $user)
            ->getQuery()
            ->getOneOrNullResult();
        if ($applicationId) {
            $this->createQueryBuilder('a')
                ->delete()
                ->andWhere('a.id = :id')
                ->setParameter('id', $applicationId['id'])
                ->getQuery()
                ->execute();
        }
    }

    public function findByTopic(Topic $topic): array
    {
        return $this->createQueryBuilder('a')
            ->select('a, c, p')
            ->leftJoin('a.candidate', 'c')
            ->leftJoin('c.profile', 'p')
            ->andWhere('a.topic = :topic')
            ->setParameter('topic', $topic)
            ->getQuery()
            ->getArrayResult();
    }
}
