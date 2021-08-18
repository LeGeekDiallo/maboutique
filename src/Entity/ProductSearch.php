<?php


namespace App\Entity;


class ProductSearch
{
    private string $keyWord;
    private int $shopId;

    /**
     * @return string
     */
    public function getKeyWord(): string
    {
        return $this->keyWord;
    }

    /**
     * @param string $keyWord
     * @return ProductSearch
     */
    public function setKeyWord(string $keyWord): ProductSearch
    {
        $this->keyWord = $keyWord;
        return $this;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     * @return ProductSearch
     */
    public function setShopId(int $shopId): ProductSearch
    {
        $this->shopId = $shopId;
        return $this;
    }
}