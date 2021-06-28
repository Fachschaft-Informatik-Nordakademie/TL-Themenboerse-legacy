<?php

namespace App\Repository;

use App\Entity\StatusType;
use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Carbon\Carbon;

class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    public function listTopics(int $page, int $pageSize, string $orderBy, string $orderDirection, ?string $text, ?string $tags, bool $onlyOpen, ?Carbon $startUntil, ?Carbon $startFrom, ?Carbon $endUntil, ?Carbon $endFrom)
    {
        $offset = $page * $pageSize;

        $query = $this->createQueryBuilder('t');

        $query = $this->addSearchTextToQuery($text, $query);
        $query = $this->addTagsToQuery($tags, $query);
        $query = $this->addOnlyOpenToQuery($onlyOpen, $query);
        $query = $this->addStartUntilToQuery($startUntil, $query);
        $query = $this->addStartFromToQuery($startFrom, $query);
        $query = $this->addEndUntilToQuery($endUntil, $query);
        $query = $this->addEndFromToQuery($endFrom, $query);

        return $query
            ->orderBy('t.' . $orderBy, $orderDirection)
            ->addOrderBy('t.id', 'asc')
            ->setMaxResults($pageSize)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();
    }

    private function addSearchTextToQuery(?string $text, QueryBuilder $query): QueryBuilder
    {
        if ($text === null) return $query;

        $query
            ->andWhere($query->expr()->like('t.title', ':text'))
            ->setParameter('text', '%' . $text . '%');

        return $query;
    }

    private function addTagsToQuery(?string $tags, QueryBuilder $query): QueryBuilder
    {
        if ($tags === null) return $query;

        $tagsArray = explode(',', $tags);

        if (empty($tagsArray)) return $query;

        $orX = $query->expr()->orX();
        $i = 0;
        foreach ($tagsArray as $value) {
            $orX->add($query->expr()->like('t.tags', ':tags' . $i));
            $query->setParameter('tags' . $i, '%' . $value . '%');
            $i++;
        }

        $query->andWhere($orX);
        return $query;
    }

    private function addOnlyOpenToQuery(bool $onlyOpen, QueryBuilder $query): QueryBuilder
    {
        if (!$onlyOpen) return $query;
        $query->andWhere('t.status = :status')
            ->setParameter('status', StatusType::OPEN);
        return $query;
    }

    private function addStartUntilToQuery(?Carbon $startUntil, QueryBuilder $query): QueryBuilder
    {
        if ($startUntil === null) return $query;
        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->lte('t.start', ':startUntil'),
                $query->expr()->isNull('t.start')
            )
        )
            ->setParameter('startUntil', $startUntil->endOfDay());
        return $query;
    }

    private function addStartFromToQuery(?Carbon $startFrom, QueryBuilder $query): QueryBuilder
    {
        if ($startFrom === null) return $query;
        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->gte('t.start', ':startFrom'),
                $query->expr()->isNull('t.start')
            )
        )
            ->setParameter('startFrom', $startFrom->startOfDay());
        return $query;
    }

    private function addEndUntilToQuery(?Carbon $endUntil, QueryBuilder $query): QueryBuilder
    {
        if ($endUntil === null) return $query;
        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->lte('t.deadline', ':endUntil'),
                $query->expr()->isNull('t.deadline')
            )
        )
            ->setParameter('endUntil', $endUntil->endOfDay());
        return $query;
    }

    private function addEndFromToQuery(?Carbon $endFrom, QueryBuilder $query): QueryBuilder
    {
        if ($endFrom === null) return $query;
        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->gte('t.deadline', ':endFrom'),
                $query->expr()->isNull('t.deadline')
            )
        )
            ->setParameter('endFrom', $endFrom->startOfDay());
        return $query;
    }
}
