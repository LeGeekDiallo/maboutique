<?php


namespace App\Entity;


class ClientSearch
{
    private string $clientSearch;

    /**
     * @return string
     */
    public function getClientSearch(): string
    {
        return $this->clientSearch;
    }

    /**
     * @param string $clientSearch
     * @return ClientSearch
     */
    public function setClientSearch(string $clientSearch): ClientSearch
    {
        $this->clientSearch = $clientSearch;
        return $this;
    }

}