<?php


namespace App\Entity;


class ShopSearch
{
    private string $shopName;
    private string $shopLocation;

    /**
     * @return string
     */
    public function getShopName(): string
    {
        return $this->shopName;
    }

    /**
     * @param string $shopName
     * @return ShopSearch
     */
    public function setShopName(string $shopName): ShopSearch
    {
        $this->shopName = $shopName;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopLocation(): string
    {
        return $this->shopLocation;
    }

    /**
     * @param string $shopLocation
     * @return ShopSearch
     */
    public function setShopLocation(string $shopLocation): ShopSearch
    {
        $this->shopLocation = $shopLocation;
        return $this;
    }

}