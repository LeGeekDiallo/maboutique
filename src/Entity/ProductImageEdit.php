<?php


namespace App\Entity;


class ProductImageEdit
{
    private string $filename;

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return ProductImageEdit
     */
    public function setFilename(string $filename): ProductImageEdit
    {
        $this->filename = $filename;
        return $this;
    }
}