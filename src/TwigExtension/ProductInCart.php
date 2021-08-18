<?php


namespace App\TwigExtension;


use App\Entity\Shop;
use App\Repository\CartRepository;
use App\Services\ConnectedUserService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductInCart extends AbstractExtension
{
    private CartRepository $repository;
    private SessionInterface $session;
    private ConnectedUserService $service;

    /**
     * @param CartRepository $repository
     * @param SessionInterface $session
     * @param ConnectedUserService $service
     */
    public function __construct(CartRepository $repository, SessionInterface $session, ConnectedUserService $service)
    {
        $this->repository = $repository;
        $this->session = $session;
        $this->service = $service;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('products', [$this, 'productInCart'])
        ];
    }

    /**
     * @return array
     */
    public function productInCart(Shop $shop):array{
        $cartNumber = $this->session->get("cartNumber");
        return $this->productsByCart($cartNumber["cartNumber"], $shop);
    }
    private function productsByCart(string $cartNumber, Shop $shop):array{
        $user = $this->service->connectedUser();
        if($user)
            return $this->repository->findBy(['user'=>$user->getId(), 'shop'=>$shop->getId()], ['createdAt'=>'DESC']);
        return $this->repository->findBy(['cartNumber'=>$cartNumber], ['createdAt'=>'DESC']);
    }

}