<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\Shop;
use App\Repository\CartRepository;
use App\Services\ConnectedUserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * CartController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Shop $shop
     * @param CartRepository $repository
     * @param SessionInterface $session
     * @param ConnectedUserService $service
     * @return Response
     */
    #[Route('/cart/panier-shop_ref={shop}', name: 'cart')]
    public function index(Shop $shop, CartRepository $repository, SessionInterface $session, ConnectedUserService $service): Response
    {
        $cartNumber = $session->get("cartNumber");
        $products = $this->productsByCart($shop,$cartNumber["cartNumber"], $repository, $service);
        return $this->render('cart/index.html.twig', [
            'theShop'=>$shop,
            'price'=>$this->price($products)
        ]);
    }

    /**
     * @param Shop $shop
     * @param string $cartNumber
     * @param CartRepository $repository
     * @param ConnectedUserService $service
     * @return array
     */
    private function productsByCart(Shop $shop, string $cartNumber, CartRepository $repository, ConnectedUserService $service):array{
        $user = $service->connectedUser();
        if($user)
            return $repository->findBy(['user'=>$user->getId(), 'shop'=>$shop->getId()], ['createdAt'=>'DESC']);
        return $repository->findBy(['cartNumber'=>$cartNumber], ['createdAt'=>'DESC']);
    }

    /**
     * @param array $products
     * @return int
     */
    private function price(array $products):int{
        $price = 0;
        foreach ($products as $product){
            $price += ($product->getProductQuantity()*$product->getProduct()[0]->getProductPrice());
        }
        return $price;
    }

    /**
     * @param Shop $shop
     * @param string $cartNumber
     * @param Product $product
     * @param Request $request
     * @param CartRepository $repository
     * @param Cart $cart
     * @return Response|null
     */
    #[Route("/cart/remove-product-from-cart/{shop}-{cartNumber}-{cart}-{product}", name: "delete_product_from_cart")]
    public function deleteProductFromCart(Shop $shop, string $cartNumber, Product $product, Request $request, CartRepository $repository, Cart $cart):?Response{
        if($this->isCsrfTokenValid('delete_from_cart'.$cartNumber, $request->get('_token'))){
            $repository->removeFromCart($cartNumber, $cart);
            $this->entityManager->flush();
            return $this->redirectToRoute('cart', [
                'shop'=>$shop->getId()
            ]);
        }
        return null;
    }
}
