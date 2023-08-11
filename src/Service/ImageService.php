<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiException;
use App\Model\Image;
use Imagick;
use ImagickException;

class ImageService
{
    private const LOCATION_UNMODIFIED_IMAGES = 'images/';
    private const LOCATION_MODIFIED_IMAGES = 'images/modified/';

    /**
     * @throws ApiException Error during image cropping or not found.
     */
    public function crop(Image $image): void
    {
        if (!file_exists(self::LOCATION_UNMODIFIED_IMAGES . $image->originalName)) {
            throw new ApiException('Failed to open the image: ' . $image->originalName, 400);
        }

        if ($image->width === null || $image->height === null) {
            throw new ApiException('Width or height for a crop operation is not defined.', 500);
        }

        $image->modifiedName = $this->generateModifiedName('crop', $image);

        if ($this->isModified($image)) {
            return;
        }

        try {
            $imagick = new Imagick(self::LOCATION_UNMODIFIED_IMAGES . $image->originalName);
            $originalWidth = $imagick->getImageWidth();
            $originalHeight = $imagick->getImageHeight();
        } catch (ImagickException $e) {
            throw new ApiException('Error during the image width/height check: ' . $image->originalName, 500);
        }

        if ($image->width > $originalWidth || $image->height > $originalHeight) {
            $imagick->clear();

            throw new ApiException('Crop size is invalid', 500);
        }

        try {
            $imagick->cropImage($image->width, $image->height, 0, 0);
            $imagick->setImageFormat("webp");
            $this->setWritable();
            $imagick->writeImage(self::LOCATION_MODIFIED_IMAGES . $image->modifiedName);
        } catch (ImagickException $e) {
            $imagick->clear();

            throw new ApiException('Error during the image cropping.', 500);
        }

        $imagick->clear();
    }

    public function resize(int $width, int $length): void
    {
        // crop image
    }

    private function generateModifiedName(string $action, Image $image): string
    {
        return sprintf('%s_%s_%s_', $action, $image->width, $image->height) . $image->originalName;
    }

    private function isModified(Image $image): bool
    {
        return file_exists(self::LOCATION_MODIFIED_IMAGES . $image->modifiedName);
    }

    public function setWritable(): void
    {
        if (!file_exists(self::LOCATION_MODIFIED_IMAGES)) {
            mkdir(self::LOCATION_MODIFIED_IMAGES, 0662);
        }

        if (!is_writable(self::LOCATION_MODIFIED_IMAGES)) {
            chmod(self::LOCATION_MODIFIED_IMAGES, 0662);
        }
    }
}