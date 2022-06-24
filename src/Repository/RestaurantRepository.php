<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Restaurant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restaurant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restaurant[]    findAll()
 * @method Restaurant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Restaurant::class);
  }

  /**
   * @return Restaurant[] Returns an array of most recents Restaurant objects
   */
  public function findMostRecents($count)
  {
    return $this->createQueryBuilder('r')
      ->orderBy('r.created_at', 'DESC')
      ->setMaxResults($count)
      ->getQuery()
      ->getResult();
  }

  /**
   * @return Restaurant[] Returns an array of Restaurant objects match search
   */
  public function findBySearch($search)
  {
    $qb = $this->createQueryBuilder('p');
    $qb->select('p');
    $qb->leftJoin('p.city', 'c');

    if ($search['zipcode']) {
      $qb->where('c.zipcode = :zipcode');
      $qb->setParameter('zipcode', $search['zipcode']);
    }

    $qb->orderBy('p.created_at', 'DESC');
    return $qb->getQuery()->getResult();
  }

  public function findBestRated($limit = 10)
  {
    return $this->createQueryBuilder('p')
      ->select('p.id, p.name, p.description, AVG(r.rating) AS average')
      ->leftJoin('p.reviews', 'r')
      ->groupBy('p.id')
      ->orderBy('average', 'DESC')
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult();
  }
}
