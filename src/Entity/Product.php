<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\Table(name="product", indexes={@ORM\Index(columns={"product_name", "product_category", "product_type", "product_brand"}, flags={"fulltext"})})
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $productName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $productCategory;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $productType;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shop;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $productBrand;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=ProductImages::class, mappedBy="product", orphanRemoval=true, cascade={"persist"})
     */
    private $productImages;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $productPrice;

    /**
     * @ORM\OneToMany(targetEntity=ProductSize::class, mappedBy="product", orphanRemoval=true, cascade={"persist"})
     */
    private $productSizes;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $sold;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price_after_sold;

    /**
     * @ORM\ManyToMany(targetEntity=Cart::class, mappedBy="product")
     */
    private $carts;



    /**
     * @ORM\ManyToMany(targetEntity=OrderItems::class, mappedBy="product")
     */
    private $orderItems;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $nbSize;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $productState;

    /**
     * @ORM\OneToMany(targetEntity=Availability::class, mappedBy="product", orphanRemoval=true)
     */
    private $availabilities;


    public function __construct()
    {
        $this->productImages = new ArrayCollection();
        $this->productSizes = new ArrayCollection();
        $this->carts = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductCategory(): ?string
    {
        return $this->productCategory;
    }

    public function setProductCategory(string $productCategory): self
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    public function getProductType(): ?string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getProductBrand(): ?string
    {
        return $this->productBrand;
    }

    public function setProductBrand(string $productBrand): self
    {
        $this->productBrand = $productBrand;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|ProductImages[]
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImages $productImage): self
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages[] = $productImage;
            $productImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImages $productImage): self
    {
        if ($this->productImages->removeElement($productImage)) {
            // set the owning side to null (unless already changed)
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    public function getProductPrice(): ?int
    {
        return $this->productPrice;
    }

    public function setProductPrice(int $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }


    /**
     * @return Collection
     */
    public function getProductSizes(): Collection
    {
        return $this->productSizes;
    }

    public function addProductSize(ProductSize $productSize): self
    {
        if (!$this->productSizes->contains($productSize)) {
            $this->productSizes[] = $productSize;
            $productSize->setProduct($this);
        }

        return $this;
    }

    public function removeProductSize(ProductSize $productSize): self
    {
        if ($this->productSizes->removeElement($productSize)) {
            // set the owning side to null (unless already changed)
            if ($productSize->getProduct() === $this) {
                $productSize->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug():string{
        return (new Slugify())->slugify($this->productName);
    }

    public function getSold(): ?float
    {
        return $this->sold;
    }

    public function setSold(?float $sold): self
    {
        $this->sold = $sold;

        return $this;
    }

    public function getPriceAfterSold(): ?float
    {
        return $this->price_after_sold;
    }

    public function setPriceAfterSold(?float $price_after_sold): self
    {
        $this->price_after_sold = $price_after_sold;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): self
    {
        if (!$this->carts->contains($cart)) {
            $this->carts[] = $cart;
            $cart->addProduct($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): self
    {
        if ($this->carts->removeElement($cart)) {
            $cart->removeProduct($this);
        }

        return $this;
    }


    /**
     * @return Collection
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItems $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->addProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItems $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            $orderItem->removeProduct($this);
        }

        return $this;
    }

    public function getNbSize(): ?int
    {
        return $this->nbSize;
    }

    public function setNbSize(?int $nbSize): self
    {
        $this->nbSize = $nbSize;

        return $this;
    }

    public function getProductState(): ?bool
    {
        return $this->productState;
    }

    public function setProductState(?bool $productState): self
    {
        $this->productState = $productState;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): self
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities[] = $availability;
            $availability->setProduct($this);
        }

        return $this;
    }

    public function removeAvailability(Availability $availability): self
    {
        if ($this->availabilities->removeElement($availability)) {
            // set the owning side to null (unless already changed)
            if ($availability->getProduct() === $this) {
                $availability->setProduct(null);
            }
        }

        return $this;
    }

    public function getTheSizesNotAdded():array{
        $availabilities = [];
        foreach ($this->getAvailabilities() as $availability){
            $availabilities[] = $availability->getProductSize();
        }
        return array_diff($this->getProductSizes()->toArray(), $availabilities);
    }

    public function getSizeAvailable():Collection{
        return $this->getAvailabilities()->filter(function ($element){
            if($element->getQuantity() > 0)
                return $element;
        });
    }
}
