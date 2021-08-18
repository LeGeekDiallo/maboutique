<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Entity\Stock;
use App\Form\StockFormType;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;

class StockController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * StockController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Shop $shop
     * @param Request $request
     * @param string $slug
     * @param StockRepository $repository
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/stock/{slug}/shop_ref={shop}', name: 'stock')]
    public function index(Shop $shop, Request $request, string $slug, StockRepository $repository): Response
    {
        $stock = new Stock();
        $form = $this->createForm(StockFormType::class, $stock);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $stock->setShop($shop)
                ->setCreatedAt(new \DateTimeImmutable('now'));
            $this->entityManager->persist($stock);
            $this->entityManager->flush();
            return $this->redirectToRoute('stock', [
                'shop'=>$shop->getId(),
                'slug'=> $slug
            ]);
        }
        return $this->render('stock/index.html.twig', [
            'shop' => $shop,
            'form'=>$form->createView(),
            'shoes'=>$repository->findBy(['category'=>'CHAUSSURES', 'shop'=>$shop->getId()]),
            'cloths'=>$repository->findBy(['category'=>'VÊTEMENTS', 'shop'=>$shop->getId()]),
            'accessories'=>$repository->findBy(['category'=>'ACCESSOIRES', 'shop'=>$shop->getId()])
        ]);
    }
}
