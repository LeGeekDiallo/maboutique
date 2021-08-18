<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Comment;
use App\Entity\ProductSearch;
use App\Entity\Shop;
use App\Entity\ShopSearch;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Form\NewUserFormType;
use App\Form\ProductSearchFormType;
use App\Form\ShopSearchFormType;
use App\Repository\ProductRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Services\ConnectedUserService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class MaBoutiqueController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * MaBoutiqueController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'ma_boutique')]
    public function index(PaginatorInterface $paginator, ShopRepository $repository, Request $request): Response
    {
        $search = new ShopSearch();
        $form = $this->createForm(ShopSearchFormType::class, $search);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $pagination = $paginator->paginate(
                $repository->shopSearch($search),
                $request->query->getInt('page', 1),
                12
            );
            return $this->render('ma_boutique/shops.html.twig', [
                'shops'=>$pagination,
                'form'=>$form->createView()
            ]);
        }
        return $this->render('ma_boutique/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @param Request $request
     * @param UserPasswordHasherInterface $encoder
     * @param NotifierInterface $notifier
     * @param UserRepository $repository
     * @return Response
     */
    #[Route('/new_user', name: 'inscription')]
    public function newUser(Request $request, UserPasswordHasherInterface $encoder, NotifierInterface $notifier, UserRepository $repository):Response{
        $user = new User();
        $form = $this->createForm(NewUserFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() and $form->isValid()){
            if($form['userType']->getData() === 'ROLE_CLIENT')
                $user->setRoles(['ROLE_CLIENT']);
            else
                $user->setRoles(['ROLE_MERCHANT']);
            $hash = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setCreatedAt(new \DateTime('now'));
            $this->entityManager->persist($user);
            if($repository->findBy(['email'=>$user->getEmail()])){
                $notifier->send(new Notification("Ce mail est déjà utilisé !", ['browser']));
                return $this->redirectToRoute('inscription');
            }
            $this->entityManager->flush();

            $notifier->send(new Notification("Vous êtes bien inscrit !", ['browser']));
            return $this->redirectToRoute('inscription');
        }
        return $this->render('ma_boutique/sign_in.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @param Shop $shop
     * @param PaginatorInterface $paginator
     * @param ProductRepository $repository
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/ma_boutique/shop-page/shop_ref={shop}-{slug}', name:"shop_page")]
    public function shop(ConnectedUserService $service, Shop $shop, PaginatorInterface $paginator, ProductRepository $repository, Request $request, SessionInterface $session):Response{
        $comment = new Comment();
        $formComment = $this->createForm(CommentFormType::class, $comment);
        $this->cartNumberHandler($session, $shop);
        $productSearch = new ProductSearch();
        $form = $this->createForm(ProductSearchFormType::class, $productSearch);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $productSearch->setShopId($shop->getId());
            $pagination = $paginator->paginate(
                $repository->findAllProductsBySearch($productSearch->getKeyWord(), $productSearch->getShopId()),
                $request->query->getInt('page', 1),
                15
            );
            return $this->render("shop/shop_page.html.twig", [
                'theShop'=>$shop,
                'theShopProducts'=>$pagination,
                'form'=>$form->createView(),
                'commentForm'=>$formComment->createView()
            ]);
        }
        //handling new comment
        $formComment->handleRequest($request);
        if($formComment->isSubmitted() and $formComment->isValid()){
            $user = $service->connectedUser();
            $comment->setShop($shop)
                ->setCreatedAt(new \DateTimeImmutable('now'));
            if($user)
                $comment->setUser($user);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            return $this->redirectToRoute("shop_page", [
                'shop'=>$shop->getId(),
                'slug'=>$shop->getSlug()
            ]);
        }
        $pagination = $paginator->paginate(
            $repository->findAllProductsByShop($shop),
            $request->query->getInt('page', 1),
            15
        );
        return $this->render("shop/shop_page.html.twig", [
            'theShop'=>$shop,
            'theShopProducts'=>$pagination,
            'form'=>$form->createView(),
            'commentForm'=>$formComment->createView(),
        ]);
    }

    /**
     * @param ConnectedUserService $service
     * @param Shop $shop
     * @param Request $request
     * @param string $category
     * @param string $type
     * @param PaginatorInterface $paginator
     * @param ProductRepository $repository
     * @return Response
     */
    #[Route('/ma_boutique/shop-page/shop_ref={shop}/category={category}&type={type}', name: 'product_by_type')]
    public function productByType(ConnectedUserService $service, Shop $shop, Request $request, string $category, string $type, PaginatorInterface $paginator, ProductRepository $repository):Response{
        $productSearch = new ProductSearch();
        $form = $this->createForm(ProductSearchFormType::class, $productSearch);
        $pagination = $paginator->paginate(
            $repository->findAllProductsByType($shop, $category, $type),
            $request->query->getInt('page', 1),
            15
        );
        $comment = new Comment();
        $formComment = $this->createForm(CommentFormType::class, $comment);
        //handling new comment
        $formComment->handleRequest($request);
        if($formComment->isSubmitted() and $formComment->isValid()){
            $user = $service->connectedUser();
            $comment->setShop($shop)
                ->setCreatedAt(new \DateTimeImmutable('now'));
            if($user)
                $comment->setUser($user);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            return $this->redirectToRoute("shop_page", [
                'shop'=>$shop->getId(),
                'slug'=>$shop->getSlug()
            ]);
        }
        return $this->render("shop/shop_page.html.twig", [
            'theShop'=>$shop,
            'theShopProducts'=>$pagination,
            'form'=>$form->createView(),
            'commentForm'=>$formComment->createView(),
        ]);
    }

    #[Route('/ma_boutique/shop-page/shop_ref={shop}/category={category}-brand={brand}', name: 'product_by_brand')]
    public function productByBrand(ConnectedUserService $service, Shop $shop, Request $request,
                                   string $category, string $brand, PaginatorInterface $paginator,
                                   ProductRepository $repository):Response{
        $productSearch = new ProductSearch();
        $form = $this->createForm(ProductSearchFormType::class, $productSearch);
        $pagination = $paginator->paginate(
            $repository->findAllProductsByBrand($shop, $category, $brand),
            $request->query->getInt('page', 1),
            15
        );
        $comment = new Comment();
        $formComment = $this->createForm(CommentFormType::class, $comment);
        //handling new comment
        $formComment->handleRequest($request);
        if($formComment->isSubmitted() and $formComment->isValid()){
            $user = $service->connectedUser();
            $comment->setShop($shop)
                ->setCreatedAt(new \DateTimeImmutable('now'));
            if($user)
                $comment->setUser($user);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            return $this->redirectToRoute("shop_page", [
                'shop'=>$shop->getId(),
                'slug'=>$shop->getSlug()
            ]);
        }
        return $this->render("shop/shop_page.html.twig", [
            'theShop'=>$shop,
            'theShopProducts'=>$pagination,
            'form'=>$form->createView(),
            'commentForm'=>$formComment->createView(),
        ]);

    }
    /**
     * @param ConnectedUserService $service
     * @param Shop $shop
     * @param Request $request
     * @param string $category
     * @param PaginatorInterface $paginator
     * @param ProductRepository $repository
     * @return Response
     */
    #[Route('/ma_boutique/shop-page/shop_ref={shop}/category={category}', name: 'product_by_cat')]
    public function productByCategory(ConnectedUserService $service, Shop $shop, Request $request,
                                      string $category, PaginatorInterface $paginator,
                                      ProductRepository $repository):Response{
        $productSearch = new ProductSearch();
        $form = $this->createForm(ProductSearchFormType::class, $productSearch);
        $pagination = $paginator->paginate(
            $repository->findAllProductsByCat($shop, $category),
            $request->query->getInt('page', 1),
            15
        );
        $comment = new Comment();
        $formComment = $this->createForm(CommentFormType::class, $comment);
        if($formComment->isSubmitted() and $formComment->isValid()){
            $user = $service->connectedUser();
            $comment->setShop($shop)
                ->setCreatedAt(new \DateTimeImmutable('now'));
            if($user)
                $comment->setUser($user);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            return $this->redirectToRoute("shop_page", [
                'shop'=>$shop->getId(),
                'slug'=>$shop->getSlug()
            ]);
        }
        return $this->render("shop/shop_page.html.twig", [
            'theShop'=>$shop,
            'theShopProducts'=>$pagination,
            'form'=>$form->createView(),
            'commentForm'=>$formComment->createView(),
        ]);
    }



    private function cartNumberHandler(SessionInterface $session, Shop $shop):void{
        $cart = $session->get("cartNumber", []);
        if(empty($cart["cartNumber"])){
            $cart["cartNumber"] = $shop->getId().$session->getId();
        }elseif($cart["cartNumber"] !== $shop->getId()){
            $cart["cartNumber"] = $shop->getId().$session->getId();
        }
        $session->set("cartNumber",$cart);
    }
}
