<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param Shop $shop
     * @return Query
     */
    public function findByQuery(Shop $shop):Query{
        return $this->createQueryBuilder('product')
            ->andWhere('product.shop = :shop_id')
            ->orderBy('product.createdAt', 'DESC')
            ->setParameter(':shop_id',$shop->getId())
            ->getQuery();
    }

    /**
     * @param Shop $shop
     * @return Query
     */
    public function findAllProductsByShop(Shop $shop):Query{
        return $this->createQueryBuilder('product')
            ->andWhere('product.shop=:shop')
            ->orderBy('product.createdAt', 'DESC')
            ->setParameter(':shop', $shop->getId())
            ->getQuery();
    }

    /**
     * @param Shop $shop
     * @param string $category
     * @return Query
     */
    public function findAllProductsByCat(Shop $shop, string $category):Query{
        return $this->createQueryBuilder('product')
            ->andWhere('product.shop=:shop', 'product.productCategory=:category')
            ->orderBy('product.createdAt', 'DESC')
            ->setParameters([
                ':shop'=>$shop->getId(),
                ':category'=>$category
            ])
            ->getQuery();
    }

    public function findAllProductsBySearch(string $keyWord, int $shopId):Query{
        return $this->createQueryBuilder('product')
            ->andWhere('MATCH_AGAINST(product.productName, product.productBrand, product.productCategory, product.productType) AGAINST(:keyWord boolean)>0',
            'product.shop=:shop')
            ->orderBy('product.createdAt', 'DESC')
            ->setParameters([
                ':shop'=>$shopId,
                ':keyWord'=>$keyWord
            ])
            ->getQuery();
    }

    /**
     * @param Product $product
     * @param Shop $shop
     * @return array
     */
    public function relatedProduct(Product $product, Shop $shop):array{
        $query = $this->createQueryBuilder('product');
        $query->andWhere('product.id !=:productId and product.shop=:shopId',
            $query->expr()->orX(
                $query->expr()->like('product.productType', ':type'),
                $query->expr()->like('product.productName', ':name'),
                $query->expr()->like('product.productBrand', ':brand')
            ))
            ->setParameters([
            ':productId'=>$product->getId(),
            ':shopId'=>$shop->getId(),
            ':type'=>$product->getProductType(),
            ':name'=>$product->getProductName(),
            ':brand'=>$product->getProductBrand()
        ])
            ->setMaxResults(5);
        return $query->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllProductsByType(Shop $shop, string $category, string $type):Query
    {
        return $this->createQueryBuilder('product')
            ->andWhere('product.shop=:shop', 'product.productCategory=:category', 'product.productType=:type')
            ->orderBy('product.createdAt', 'DESC')
            ->setParameters([
                ':shop'=>$shop->getId(),
                ':category'=>$category,
                ':type'=>$type
            ])
            ->getQuery();
    }

    public function findAllProductsByBrand(Shop $shop, string $category, string $brand)
    {
        return $this->createQueryBuilder('product')
            ->andWhere('product.shop=:shop', 'product.productCategory=:category', 'product.productBrand=:brand')
            ->orderBy('product.createdAt', 'DESC')
            ->setParameters([
                ':shop'=>$shop->getId(),
                ':category'=>$category,
                ':brand'=>$brand
            ])
            ->getQuery();
    }
}
