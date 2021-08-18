<?php

namespace App\Controller;

use App\Entity\ClientSearch;
use App\Entity\Image;
use App\Entity\Shop;
use App\Entity\User;
use App\Form\ClientSearchType;
use App\Form\ImageFormType;
use App\Form\ShopFormType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * ShopController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/shop/new_shop_welcome', name: 'new_shop_welcome')]
    public function newShopMiddle():Response{
        return $this->render('shop/new_shop_welcome.html.twig');
    }
    /**
     * @param User $merchant
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param NotifierInterface $notifier
     * @return Response
     */
    #[Route('/shop/new_shop/{merchant}', name: 'new_shop')]
    public function createNewShop(User $merchant, Request $request, FileUploader $fileUploader, NotifierInterface $notifier): Response
    {
        $shop = new Shop();
        $form = $this->createForm(ShopFormType::class, $shop);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $shop->setCreatedAt(new \DateTimeImmutable('now'))
                ->setMerchant($merchant);
            if($file = $form['shopLogo']->getData()){
                $filename = $fileUploader->uploadShopLogo($file);
                $shop->setShopLogo($filename);
            }
            $this->entityManager->persist($shop);
            $this->entityManager->flush();
            $notifier->send(new Notification("La boutique a été bien ouverte !", ['browser']));
            return $this->redirectToRoute('back_shop');
        }
        return $this->render('shop/new_shop.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Shop $shop
     * @param FileUploader $fileUploader
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/shop/new_image/{shop}', name: 'new_image')]
    public function changeImage(Request $request, Shop $shop, FileUploader $fileUploader):Response{
        $image = new Image();
        $form = $this->createForm(ImageFormType::class, $image);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $fileUploader->delete($shop->getShopLogo());
            $file = $form['filename']->getData();
            $filename = $fileUploader->uploadShopLogo($file);
            $shop->setShopLogo($filename);

            $this->entityManager->flush();
            return $this->redirectToRoute('back_shop');
        }
        return $this->render('shop/change_image.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Shop $shop
     * @param PaginatorInterface $paginator
     * @param ProductRepository $repository
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/shop/products/shop_ref={shop}', name: 'products')]
    public function products(Shop $shop, PaginatorInterface $paginator, ProductRepository $repository, Request $request):Response{
        $pagination = $paginator->paginate(
            $repository->findByQuery($shop), /* query NOT result */
            $request->query->getInt('page', 1),
            15
        );
        return $this->render('shop/products.html.twig', [
            'shop'=>$shop,
            'pagination'=>$pagination
        ]);
    }

    /**
     * @param Shop $shop
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/shop/shop-client/shop_ref={shop}', name: 'shop_clients')]
    public function shopClients(Shop $shop, Request $request):Response{
        $search = new ClientSearch();
        $form = $this->createForm(ClientSearchType::class, $search);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            return $this->render('shop/shop_clients.html.twig', [
                'clients'=> $this->clientsMatch($shop->getClients(), $search),
                'form'=>$form->createView()
            ]);
        }
        return $this->render('shop/shop_clients.html.twig', [
            'clients'=>$shop->getClients(),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @param Collection $shopClients
     * @param ClientSearch $searchCriteria
     * @return array
     */
    private function clientsMatch(Collection $shopClients, ClientSearch $searchCriteria):array{
        $clientsFounds = [];
        foreach ($shopClients as $client){
            if (strcasecmp($client->getUserName(), $searchCriteria->getClientSearch()) === 0 or
                strcasecmp($client->getPhoneNumber(), $searchCriteria->getClientSearch()) === 0 or
                str_contains(strtolower($client->getUserName()), strtolower($searchCriteria->getClientSearch()))){
                $clientsFounds[]=$client;
            }
        }
        return $clientsFounds;
    }
}
