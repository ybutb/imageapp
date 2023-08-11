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
    public function crop(Image $image): Image
    {
        $this->isOriginalImageExists($image);

        if ($image->width === null || $image->height === null) {
            throw new ApiException('Width or height for a crop operation is not defined.', 500);
        }

        $image->modifiedName = $this->generateModifiedName('crop', $image);

        if ($this->isModified($image)) {
            return $image;
        }

        try {
            $imagick = $this->getImagick(self::LOCATION_UNMODIFIED_IMAGES . $image->originalName);
            $imagick->cropImage($image->width, $image->height, 0, 0);
            $imagick->setImageFormat($image->getExtension());
            $imagick->writeImage(self::LOCATION_MODIFIED_IMAGES . $image->modifiedName);
        } catch (ImagickException $e) {
            throw new ApiException('Error during the image cropping ' . $image->originalName, 500);
        }

        $imagick->clear();

        return $image;
    }

    protected function getImagick(string $file): Imagick
    {
        return new Imagick($file);
    }

    public function resize(Image $image): Image
    {
        $this->isOriginalImageExists($image);

        if ($image->width === null && $image->height === null) {
            throw new ApiException('Width or height for a crop operation is not defined.', 500);
        }

        $image->modifiedName = $this->generateModifiedName('resize', $image);

        if ($this->isModified($image)) {
            return $image;
        }

        try {
            $imagick = $this->getImagick(self::LOCATION_UNMODIFIED_IMAGES . $image->originalName);
            $imagick->resizeImage($image->width, $image->height, Imagick::FILTER_UNDEFINED, 1);
            $imagick->writeImage(self::LOCATION_MODIFIED_IMAGES . $image->modifiedName);
        } catch (ImagickException $e) {
            throw new ApiException('Error during the image resize: ' . $image->originalName, 500);
        }

        $imagick->clear();

        return $image;
    }

    protected function isOriginalImageExists(Image $image)
    {
        if (!file_exists(self::LOCATION_UNMODIFIED_IMAGES . $image->originalName)) {
            throw new ApiException('Failed to find the image: ' . $image->originalName, 404);
        }
    }

    private function generateModifiedName(string $action, Image $image): string
    {
        return sprintf('%s_%s_%s_', $action, $image->width, $image->height) . $image->originalName;
    }

    private function isModified(Image $image): bool
    {
        return file_exists(self::LOCATION_MODIFIED_IMAGES . $image->modifiedName);
    }

    public function getSampleImagesPaths(): array
    {
        return [
            self::LOCATION_UNMODIFIED_IMAGES . 'cropped_dog.webp',
            self::LOCATION_UNMODIFIED_IMAGES . 'resized_dog.webp',
        ];
    }
}