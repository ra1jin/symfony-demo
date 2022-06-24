<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, City::class);
        $this->em = $entityManager;
    }

    public function createIfNotExist($value)
    {
        $cityFound = $this->findOneBy([
            'name' => $value['name'],
            'zipcode' => $value['zipcode']
        ]);

        if ($cityFound) {
            return $cityFound;
        } else {
            $city = new City();
            $city->setName($value['name']);
            $city->setZipcode($value['zipcode']);
            $this->em->persist($city);
            $this->em->flush();

            return $city;
        }
    }
}
