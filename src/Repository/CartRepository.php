<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function removeFromCart(string $cartNumber, Cart $cart){
        return $this->createQueryBuilder("cart")
            ->delete()
            ->andWhere("cart.cartNumber=:cartNumber", "cart.id=:cartId")
            ->setParameters([":cartNumber"=>$cartNumber, ":cartId"=>$cart->getId()])
            ->getQuery()
            ->getResult();
    }

    public function updateUser(string $cartNumber, User $user){
        return $this->createQueryBuilder("cart")
            ->update()
            ->set('cart.user', ':userId')
            ->andWhere("cart.cartNumber=:cartNumber")
            ->setParameters([":cartNumber"=>$cartNumber, ":userId"=>$user->getId()])
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Cart[] Returns an array of Cart objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function removeFromCartByUser(User $user){
        return $this->createQueryBuilder("cart")
            ->delete()
            ->andWhere("cart.user=:user")
            ->setParameter(":user", $user)
            ->getQuery()
            ->getResult();
    }
}
