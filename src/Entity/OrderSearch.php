<?php


namespace App\Entity;


class OrderSearch
{
    private string $orderNumber;

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     * @return OrderSearch
     */
    public function setOrderNumber(string $orderNumber): OrderSearch
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }


}