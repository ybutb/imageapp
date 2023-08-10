<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ImageModificationException;
use App\Model\Image;
use HttpException;
use Imagick;
use ImagickException;
use InvalidArgumentException;

class ImageService
{
    private const LOCATION_UNMODIFIED_IMAGES = 'public/images/';
    private const LOCATION_MODIFIED_IMAGES = 'public/images/modified/';

    /**
     * @throws HttpException Image not found or wrong image arguments.
     * @throws ImageModificationException Error during image cropping.
     */
    public function crop(Image $image): void
    {
        if (!file_exists(self::LOCATION_UNMODIFIED_IMAGES . $image->initialName)) {
            throw new HttpException('Image not found', 404);
        }

        if ($image->width === null || $image->height === null) {
            throw new HttpException('Width or height for a crop operation is not defined.', 500);
        }

        try {
            $imagick = new Imagick(self::LOCATION_UNMODIFIED_IMAGES . $image->initialName);
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();
        } catch (\ImagickException $e) {
            throw new ImageModificationException('Failed to open the image: ' . $image->initialName);
        }

        if ($image->width > $width || $image->height > $height) {
            throw new ImageModificationException('Crop size is invalid');
        }

        $startWidth = ($width - $image->width) / 2;
        $startHeight = ($height - $image->height) / 2;

        $image->modifiedName = $this->generateImageName('crop', $image);

        try {
            $imagick->cropImage($image->width, $image->height, $startWidth, $startHeight);
            $imagick->setImageFormat("webp");
            $imagick->writeImage(self::LOCATION_MODIFIED_IMAGES . $image->modifiedName);
        } catch (ImagickException $e) {
            throw new ImageModificationException('Error during image cropping.');
        }

        $imagick->clear();
        $imagick->destroy();
    }

    public function resize(int $width, int $length): void
    {
        // crop image
    }

    private function generateImageName(string $action, Image $image): string
    {
        return sprintf('%s_%s_%s_', $action, $image->width, $image->height) . $image->initialName;
    }
}