<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Topic;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function hasCandidateForTopic(int $user, int $topic): bool
    {
        $query = $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->join(Topic::class, 't', Expr\Join::WITH, 't.id = :t_id')
            ->join(User::class, 'u', Expr\Join::WITH, 'u.id = :u_id')
            ->setParameter('t_id', $topic)
            ->setParameter('u_id', $user)
            ->getQuery();
        return $query->getSingleScalarResult() > 0;
    }
}
