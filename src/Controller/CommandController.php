<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\OrderItems;
use App\Entity\OrderSearch;
use App\Entity\Shop;
use App\Entity\User;
use App\Form\OrderSearchType;
use App\Repository\AvailabilityRepository;
use App\Repository\CartRepository;
use App\Repository\CommandRepository;
use App\Services\ConnectedUserService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * CommandController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Shop $shop
     * @param User $user
     * @param CartRepository $repository
     * @param int $price
     * @param AvailabilityRepository $avRepository
     * @return Response
     */
    #[Route('/command/shop_ref={shop}-{user}-{price}', name: 'new_command')]
    public function newCommand(Shop $shop, User $user, CartRepository $repository, int $price, AvailabilityRepository $avRepository): Response
    {
        $newCommand = new Command();
        $orderItem = new OrderItems();
        $carts = $user->getCarts();
        $newCommand->setUser($user)
            ->setShop($shop)
            ->setCreatedAt(new \DateTimeImmutable('now'))
            ->setTotalPrice($price)
            ->setOrderState("new order");
        foreach ($carts as $cart){
            $product = $cart->getProduct()[0];
            $orderItem->addProduct($product);
            $orderItem->setItemSize($cart->getProductSize())
                ->setQuantity($cart->getProductQuantity());
        }
        $newCommand->addOrderItems($orderItem);
        if(!$shop->hasThisClient($user->getId())) {
            $shop->addClient($user);
        }
        $this->entityManager->persist($newCommand);
        $repository->removeFromCartByUser($user);
        $this->entityManager->flush();
        //vider le panier, utilisation du messenger
        return $this->redirectToRoute('order_done');
    }

    /**
     * @param int $productId
     * @param string $size
     * @param AvailabilityRepository $repository
     * @param int $quantity
     */
    private function updateAvailability(int $productId, string $size, AvailabilityRepository $repository, int $quantity):void{
        $availability = $repository->findOneBy(['product'=>$productId, 'ProductSize'=>$size]);
        $availability->setQuantity($availability->getQuantity()-$quantity);
    }
    #[Route('/command/order-done', name: 'order_done')]
    public function orderDone():Response{
        return $this->render('command/order_done.html.twig');
    }
    /**
     * @param CartRepository $repository
     * @param Shop $shop
     * @param int $price
     * @param ConnectedUserService $service
     * @param SessionInterface $session
     * @return Response
     * @IsGranted("ROLE_CLIENT")
     */
    #[Route('/command/recap/{price}-shop_ref={shop}', name: 'command_recap')]
    public function commandRecap(CartRepository $repository, Shop $shop, int $price, ConnectedUserService $service, SessionInterface $session):Response{
        $user = $service->connectedUser();
        $cartNumber = $session->get("cartNumber");
        $repository->updateUser($cartNumber["cartNumber"], $user);

        return $this->render('command/command_recap.html.twig', [
            'price'=>$price,
            'carts'=> $user->getCarts(),
            'theShop'=>$shop
        ]);
    }

    /**
     * @param Shop $shop
     * @param CommandRepository $repository
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/command/shop-orders/shop_ref={shop}-{slug}', name: 'shop_orders')]
    public function shopOrders(Shop $shop, CommandRepository $repository, Request $request):Response{
        $search = new OrderSearch();
        $form = $this->createForm(OrderSearchType::class, $search);
        $form->handleRequest($request);
        if($form->isSubmitted() and  $form->isSubmitted()){
            return $this->render('command/shop_orders.html.twig', [
                'orders'=>$repository->getOrderBySearch($search, $shop),
                'form'=>$form->createView()
            ]);
        }
        return $this->render('/command/shop_orders.html.twig', [
            'orders'=>$repository->findBy(['shop'=>$shop->getId(), 'orderState'=>'new order'], ['createdAt'=>'DESC']),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @param Shop $shop
     * @param CommandRepository $repository
     * @param string $state
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/command/shop-orders-by-state/shop_ref={shop}-state={state}', name: 'orders_by_state')]
    public function ordersByState(Shop $shop, CommandRepository $repository, string $state, Request $request):Response{
        $search = new OrderSearch();
        $form = $this->createForm(OrderSearchType::class, $search);
        $form->handleRequest($request);
        if($form->isSubmitted() and  $form->isSubmitted()){
            return $this->render('command/shop_orders.html.twig', [
                'orders'=>$repository->getOrderBySearch($search, $shop),
                'form'=>$form->createView()
            ]);
        }
        if($state === "all"){
            return $this->render('/command/shop_orders.html.twig', [
                'orders'=>$repository->findBy(['shop'=>$shop->getId()], ['createdAt'=>'DESC']),
                'form'=>$form->createView()
            ]);
        }
        return $this->render('/command/shop_orders.html.twig', [
            'orders'=>$repository->findBy(['shop'=>$shop->getId(), 'orderState'=>$state], ['createdAt'=>'DESC']),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @param Command $command
     * @param Shop $shop
     * @param AvailabilityRepository $repository
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/command/order-confirmation/{command}-{shop}-{slug}', name: 'order_confirmation')]
    public function confirmed(Command $command, Shop $shop, AvailabilityRepository $repository):Response{
        $command->setOrderState("confirmed");
        $orderItems = $command->getOrderItems();
        foreach ($orderItems as $item){
            $quantity = $item->getQuantity();
            $size = $item->getItemSize();
            $products = $item->getProduct();
            foreach ($products as $product){
                $this->updateAvailability($product->getId(), $size, $repository, $quantity);
            }
        }
        $this->entityManager->flush();
        return $this->redirectToRoute('shop_orders', [
            'shop'=>$shop->getId(),
            'slug'=>$shop->getSlug()
        ]);
    }

    /**
     * @param Command $command
     * @param Shop $shop
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/command/order-canceling/{command}-{shop}-{slug}', name: 'order_canceling')]
    public function canceled(Command $command, Shop $shop):Response{
        $command->setOrderState("canceled");
        $this->entityManager->flush();
        return $this->redirectToRoute('shop_orders', [
            'shop'=>$shop->getId(),
            'slug'=>$shop->getSlug()
        ]);
    }
}
