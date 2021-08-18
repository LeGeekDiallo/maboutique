<?php


namespace App\Services;


use App\Entity\Activity;
use App\Entity\ActivityImage;
use App\Entity\Apartment;
use App\Entity\ApartmentImages;
use App\Entity\Car;
use App\Entity\CarImages;
use App\Entity\HouseVilla;
use App\Entity\HouseVillaImages;
use App\Entity\OfficeShopLand;
use App\Entity\OfficeShopLandImages;
use App\Entity\Product;
use App\Entity\ProductImages;
use App\Entity\Property;
use App\Entity\PropertyImages;
use App\Entity\Studio;
use App\Entity\StudioImages;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private string $targetDirectory;
    private string $productImageDirectory;
    private SluggerInterface $slugger;

    private const MAX_WIDTH = 2880;
    private const MAX_HEIGHT = 1920;
    private const MAX_WIDTH_CLOGO = 300;

    private const MAX_HEIGHT_CLOGO = 290;
    private Imagine $imagine;
    /**
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    /**
     * @return string
     */
    public function getProductImageDirectory(): string
    {
        return $this->productImageDirectory;
    }

    /**
     * FileUploader constructor.
     * @param string $targetDirectory
     * @param string $productImageDirectory
     * @param SluggerInterface $slugger
     */
    public function __construct(string $targetDirectory, string $productImageDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->productImageDirectory = $productImageDirectory;
        $this->slugger = $slugger;
        $this->imagine = new Imagine();
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function upload(UploadedFile $file):string{
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(),$fileName);
            $this->resize($this->getTargetDirectory().'/'.$fileName);
        }catch (FileException $exception){
            die("file exception");
        }
        return $fileName;
    }

    private function productImageUpload(UploadedFile $file):string{
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getProductImageDirectory(),$fileName);
            $this->resize($this->getProductImageDirectory().'/'.$fileName);
        }catch (FileException $exception){
            die("file exception");
        }
        return $fileName;
    }
    /**
     * @param UploadedFile $file
     * @return string
     */
    public function uploadShopLogo(UploadedFile $file):string{
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(),$fileName);
            $this->resizeShopLogo($this->getTargetDirectory().'/'.$fileName);
        }catch (FileException $exception){
            die("file exception");
        }
        return $fileName;
    }

    /**
     * @param $files
     * @param Product $product
     */
    public function uploadProductImages($files, Product $product):void{
        foreach ($files as $file){
            $filename = $this->productImageUpload($file);
            $image = new ProductImages();
            $image->setFilename($filename);
            $image->setCreatedAt(new \DateTimeImmutable('now'));
            $product->addProductImage($image);
        }
    }
    /**
     * resizing avatar image
     * @param string $filename
     */
    public function resize(string $filename){
        $photo = $this->imagine->open($filename);
        $photo->resize(new Box(self::MAX_WIDTH, self::MAX_HEIGHT))->save($filename);
    }

    /**
     * @param string $filename
     * resize the company's logo
     */
    public function resizeShopLogo(string $filename){
        $photo = $this->imagine->open($filename);
        $photo->resize(new Box(self::MAX_WIDTH_CLOGO, self::MAX_HEIGHT_CLOGO))->save($filename);
    }

    public function delete($filename){
        if($filename !== "" && $filename!==null)
            unlink($this->getTargetDirectory().'/'.$filename);
    }

    /**
     * @param string $filename
     */
    public function deleteProductImage(string $filename):void{
        if($filename !== "" && $filename!==null)
            unlink($this->getProductImageDirectory().'/'.$filename);
    }
}