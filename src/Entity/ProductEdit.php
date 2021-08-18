<?php


namespace App\Entity;


use DateTimeImmutable;

class ProductEdit
{
    private string $productName;
    private string $productCategory;
    private string $productType;
    private int $productPrice;
    private string $productGender;
    private DateTimeImmutable $createdAt;
    private string $productBrand;

    /**
     * @return string
     */
    public function getProductBrand(): string
    {
        return $this->productBrand;
    }

    /**
     * @param string $productBrand
     * @return ProductEdit
     */
    public function setProductBrand(string $productBrand): ProductEdit
    {
        $this->productBrand = $productBrand;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     * @return ProductEdit
     */
    public function setProductName(string $productName): ProductEdit
    {
        $this->productName = $productName;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductCategory(): string
    {
        return $this->productCategory;
    }

    /**
     * @param string $productCategory
     * @return ProductEdit
     */
    public function setProductCategory(string $productCategory): ProductEdit
    {
        $this->productCategory = $productCategory;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductType(): string
    {
        return $this->productType;
    }

    /**
     * @param string $productType
     * @return ProductEdit
     */
    public function setProductType(string $productType): ProductEdit
    {
        $this->productType = $productType;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductPrice(): int
    {
        return $this->productPrice;
    }

    /**
     * @param int $productPrice
     * @return ProductEdit
     */
    public function setProductPrice(int $productPrice): ProductEdit
    {
        $this->productPrice = $productPrice;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductGender(): string
    {
        return $this->productGender;
    }

    /**
     * @param string $productGender
     * @return ProductEdit
     */
    public function setProductGender(string $productGender): ProductEdit
    {
        $this->productGender = $productGender;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return ProductEdit
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): ProductEdit
    {
        $this->createdAt = $createdAt;
        return $this;
    }

}