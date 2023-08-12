<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiException;
use App\Model\Image;
use ImagickException;

class ImageService
{
    private const LOCATION_UNMODIFIED_IMAGES = 'images/';
    private const LOCATION_MODIFIED_IMAGES = 'images/modified/';

    public function __construct(private readonly ImagickFacade $imagickFacade)
    {
    }

    /**
     * @throws ApiException Error during image cropping or not found / modification params were not provided.
     */
    public function modify(Image $image): void
    {
        $this->isOriginalImageExists($image);

        if (!$image->width || !$image->height) {
            throw new ApiException('Width or height for a crop operation is not defined.', 500);
        }

        $image->modifiedName = $this->generateModifiedName($image->modificationType, $image);

        if ($this->isModified($image)) {
            return;
        }

        $modificationAction = $image->modificationType;

        try {
            $this->imagickFacade->$modificationAction(
                self::LOCATION_UNMODIFIED_IMAGES . $image->originalName,
                self::LOCATION_MODIFIED_IMAGES . $image->modifiedName,
                $image
            );
        } catch (ImagickException $e) {
            throw new ApiException('Error during the image modification ' . $image->originalName, 500);
        }
    }

    protected function isOriginalImageExists(Image $image): void
    {
        if (!file_exists(self::LOCATION_UNMODIFIED_IMAGES . $image->originalName)) {
            throw new ApiException('Failed to find the image: ' . $image->originalName, 404);
        }
    }

    private function generateModifiedName(string $action, Image $image): string
    {
        return sprintf('%s_%s_%s_', $action, $image->width, $image->height) . $image->originalName;
    }

    protected function isModified(Image $image): bool
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