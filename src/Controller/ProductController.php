<?php

namespace App\Controller;

use App\Entity\Availability;
use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\ProductEdit;
use App\Entity\ProductImageEdit;
use App\Entity\ProductImages;
use App\Entity\ProductSearch;
use App\Entity\ProductSize;
use App\Entity\Shop;
use App\Form\AvailabilityType;
use App\Form\CartFormType;
use App\Form\CommentFormType;
use App\Form\ImageEditFormType;
use App\Form\ImageFormType;
use App\Form\ProductEditFormType;
use App\Form\ProductFormType;
use App\Form\ProductSearchFormType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use App\Services\FileUploader;
use App\Services\ConnectedUserService;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * ProductController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param Shop $shop
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/product/new_product/shop_ref={shop}-{slug}', name: 'new_product')]
    public function newProduct(Request $request, FileUploader $fileUploader, Shop $shop): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $product->setCreatedAt(new \DateTimeImmutable('now'))
                ->setShop($shop);
            if($images = $form['productImages']->getData()){
                $fileUploader->uploadProductImages($images, $product);
            }
            $productSizes = $form['productSizes']->getData();
            $this->addSizeToProduct($productSizes, $product);
            $product->setNbSize(count($product->getProductSizes()))
                ->setProductState(false);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            return $this->redirectToRoute('new_quantity', [
                'shop'=>$shop->getId(),
                'product'=>$product->getId(),
                'sizeState'=>true
            ]);
        }
        return $this->render('product/new_product.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Product $product
     * @param Shop $shop
     * @param bool $sizeState
     * @return Response
     */
    #[Route('/product/add_new_quantity/{shop}-new-quantity-{product}-{sizeState}', name: 'new_quantity')]
    public function addProductSizeQuantity(Product $product, Shop $shop, string $sizeState):Response{
        return $this->render('/product/add_quantity.html.twig', [
            'shop'=>$shop,
            'product'=>$product,
            'sold_out'=>$sizeState
        ]);
    }

    /**
     * @param Shop $shop
     * @param Product $product
     * @param int $sizeIndex
     * @param StockRepository $repository
     * @return Response
     */
    #[Route('/product/is_in_stock/{shop}/{product}/{sizeIndex}', name: 'is_in_stock')]
    public function isInStock(Shop $shop, Product $product, int $sizeIndex, StockRepository $repository):Response{
        $stock = $shop->getStocks()[0];
        $stockItem = $stock->isInStock($shop, $product->getProductSizes()[$sizeIndex], $product->getProductType(),
            $product->getProductCategory(), $product->getProductBrand(), $repository);
        if($stockItem)
            return $this->redirectToRoute('availability', [
                'product'=>$product->getId(),
                'shop'=>$shop->getId(),
                'sizeIndex'=>$sizeIndex,
                'sizeState'=>"new"
            ]);
        return $this->redirectToRoute('new_quantity', [
            'shop'=>$shop->getId(),
            'product'=>$product->getId(),
            'sizeState'=>"out"
        ]);
    }

    /**
     * @param Shop $shop
     * @param Request $request
     * @param Product $product
     * @param int $sizeIndex
     * @param StockRepository $repository
     * @return Response
     */
    #[Route('/product/new-product/availability/{product}-shop_ref={shop}-{sizeIndex}', name: 'availability')]
    public function availability(Shop $shop, Request $request, Product $product, int $sizeIndex, StockRepository $repository):Response{
        $availability = new Availability();
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            if($product->getNbSize() > 0) {
                $product->setNbSize($product->getNbSize()-1);
                $availability->setProductSize($product->getProductSizes()[$sizeIndex]);
                $availability->setProduct($product);
                $product->setProductState(true);
                $stockItem = $repository->findOneBy(['shop'=>$shop->getId(), 'type'=>$product->getProductType(),
                    'size'=>$availability->getProductSize(), 'category'=>$product->getProductCategory(), 'brand'=>$product->getProductBrand()]);
                $stockItem->setQuantity($stockItem->getQuantity() - $availability->getQuantity());
                $this->entityManager->persist($availability);
                $this->entityManager->flush();
                if($product->getNbSize()==0)
                    return $this->redirectToRoute('products', [
                        'shop'=>$shop->getId()
                    ]);
                return $this->redirectToRoute('new_quantity', [
                    'shop'=>$shop->getId(),
                    'product'=>$product->getId(),
                    'sizeState'=>'out'
                ]);
            }
        }
        return $this->render('/product/product_availability.html.twig', [
            'form'=>$form->createView(),
            'product'=>$product,
            'shop'=>$shop,
        ]);
    }
    /**
     * @param array $sizes
     * @param Product $product
     */
    private function addSizeToProduct(array $sizes, Product $product):void{
        foreach ($sizes as $size){
            $newSize = new ProductSize();
            $newSize->setSize($size);
            $product->addProductSize($newSize);
        }
    }

    /**
     * @param Product $product
     * @param Request $request
     * @param NotifierInterface $notifier
     * @param Shop $shop
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/product/edit_product/shop_ref={shop}-edit-{product}', name: "edit_product")]
    public function editProduct(Product $product, Request $request, NotifierInterface $notifier, Shop $shop):Response{
        $edit = new ProductEdit();
        $form = $this->createForm(ProductEditFormType::class, $edit);
        $form->handleRequest($request);

        if($form->isSubmitted() and $form->isValid()){
            $edit->setCreatedAt(new \DateTimeImmutable('now'));
            $product->setProductBrand($edit->getProductBrand())
                ->setProductCategory($edit->getProductCategory())
                ->setProductName($edit->getProductName())
                ->setProductPrice($edit->getProductPrice())
                ->setCreatedAt($edit->getCreatedAt());

            $this->entityManager->flush();
            $notifier->send(new Notification("Modification avec succès !", ['browser']));
            return $this->redirectToRoute('products', [
                'shop'=>$shop->getId()
            ]);
        }
        return $this->render('product/edit_product.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @param Product $product
     * @param Request $request
     * @param NotifierInterface $notifier
     * @param FileUploader $fileUploader
     * @return Response
     * @IsGranted("ROLE_MERCHANT")
     */
    #[Route('/product/image_edit/image-product_ref={product}', name: 'product_image')]
    public function editImages(Product $product, Request $request, NotifierInterface $notifier, FileUploader $fileUploader):Response{
        $newImages = new ProductImageEdit();
        $form = $this->createForm(ImageEditFormType::class, $newImages);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            if($images = $form['filename']->getData()){
                $fileUploader->uploadProductImages($images, $product);
            }
            $this->entityManager->flush();
            $notifier->send(new Notification("Modification avec succès !", ['browser']));
            return $this->redirectToRoute('product_image', [
                'product'=>$product->getId()
            ]);
        }
        return $this->render('product/product_image.html.twig', [
            'form'=>$form->createView(),
            'product'=>$product
        ]);
    }

    /**
     * @param Product $product
     * @param Request $request
     * @param NotifierInterface $notifier
     * @param FileUploader $fileUploader
     * @param Shop $shop
     * @return Response|null
     */
    #[Route('/product/delete_product/delete-product-{product}-{shop}', name: 'delete_product')]
    public function deleteProduct(Product $product, Request $request, NotifierInterface $notifier, FileUploader $fileUploader, Shop $shop):?Response{
        if($this->isCsrfTokenValid('delete'.$product->getId(), $request->get('_token'))){
            foreach ($product->getProductImages() as $image){
                $fileUploader->deleteProductImage($image->getFilename());
            }
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            $notifier->send(new Notification("Un produit a bien été supprimée !", ['browser']));
            return $this->redirectToRoute('products', [
                'shop'=>$shop->getId()
            ]);
        }
        return null;
    }

    /**
     * @param Product $product
     * @param ProductImages $image
     * @param FileUploader $fileUploader
     * @return Response
     */
    #[Route('/product/delete_product_image/{product}-{image}', name: 'delete_product_image')]
    public function deleteProductImage(Product $product, ProductImages $image, FileUploader $fileUploader):Response{
        $fileUploader->deleteProductImage($image->getFilename());
        $product->removeProductImage($image);
        $this->entityManager->flush();

        return $this->json(["status"=>"Ok"]);
    }

    /**
     * @param ConnectedUserService $service
     * @param Product $product
     * @param Shop $shop
     * @param SessionInterface $session
     * @param Request $request
     * @param ProductRepository $repository
     * @return Response
     */
    #[Route('/product/product-details/shop_ref={shop}-{product}-{slug}', name: 'product_details')]
    public function productDetails(ConnectedUserService $service, Product $product, Shop $shop,
                                   SessionInterface $session, Request $request,
                                   PaginatorInterface $paginator,
                                   ProductRepository $repository):Response{
        $cartNumber = $session->get("cartNumber");
        $cart = new Cart();
        $form = $this->createForm(CartFormType::class, $cart, ['product_sizes'=>$product]);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $cart->setCartNumber($cartNumber['cartNumber'])
                ->setCreatedAt(new \DateTimeImmutable('now'))
                ->setShop($shop)
                ->addProduct($product);
            $user = $service->connectedUser();
            if($user)
                $cart->setUser($user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
            return $this->redirectToRoute('cart', [
                'shop'=>$shop->getId(),
            ]);
        }
        $productSearch = new ProductSearch();
        $formSearch = $this->createForm(ProductSearchFormType::class, $productSearch);
        $comment = new Comment();
        $formComment = $this->createForm(CommentFormType::class, $comment);
        $formSearch->handleRequest($request);
        if($formSearch->isSubmitted() and $formSearch->isValid()){
            $productSearch->setShopId($shop->getId());
            $pagination = $paginator->paginate(
                $repository->findAllProductsBySearch($productSearch->getKeyWord(), $productSearch->getShopId()),
                $request->query->getInt('page', 1),
                15
            );
            return $this->render("shop/shop_page.html.twig", [
                'theShop'=>$shop,
                'theShopProducts'=>$pagination,
                'form'=>$formSearch->createView(),
                'commentForm'=>$formComment->createView()
            ]);
        }
        return $this->render('/product/product_details.html.twig', [
            'product'=>$product,
            'theShop'=>$shop,
            'form'=>$formSearch->createView(),
            'formCart'=>$form->createView(),
            'relatedProducts'=>$repository->relatedProduct($product, $shop)
        ]);
    }
}
