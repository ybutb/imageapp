<?php

namespace App\Service;

use App\Model\Image;
use Imagick;

class ImagickFacade
{
    public function __construct(private readonly Imagick $imagick)
    {
    }

    public function resize(string $source, string $destination, Image $image): void
    {
        $this->imagick->readImage($source);
        $this->imagick->resizeImage($image->width, $image->height, Imagick::FILTER_LANCZOS, 1);
        $this->imagick->writeImage($destination);
        $this->imagick->clear();
    }

    public function crop(string $source, string $destination, Image $image): void
    {
        $this->imagick->readImage($source);
        $this->imagick->cropImage($image->width, $image->height, 0, 0);
        $this->imagick->writeImage($destination);
        $this->imagick->clear();
    }
}
