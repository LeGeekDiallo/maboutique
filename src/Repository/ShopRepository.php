<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\ShopSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Shop|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shop|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shop[]    findAll()
 * @method Shop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    public function shopSearch(ShopSearch $search):Query{
        $query = $this->createQueryBuilder('shop');

        $query->andWhere('MATCH_AGAINST(shop.shopName) AGAINST(:shop_name boolean)>0')
            ->orWhere($query->expr()->orX(
                $query->expr()->like('shop.city', ':city'),
                $query->expr()->like('shop.municipality', ':municipality'),
                $query->expr()->like('shop.district', ':district')
            ))
            ->orderBy('shop.createdAt', 'DESC')
            ->setParameters([
                ':shop_name'=>$search->getShopName(),
                ':city'=>$search->getShopLocation(),
                ':municipality'=>$search->getShopLocation(),
                ':district'=>$search->getShopLocation()
            ]);

        return $query->getQuery();
    }


    // /**
    //  * @return Shop[] Returns an array of Shop objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Shop
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
