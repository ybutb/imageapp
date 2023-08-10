<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ImageModificationException;
use App\Model\Image;
use App\Service\ImageService;
use App\Validator\ImageRequestValidator;
use HttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly Request $request
    ) {
    }

    /**
     * @throws HttpException
     */
    public function index(string $imageName, string $imageAction): Response
    {
        if (!ImageRequestValidator::validate($this->request)) {
            throw new HttpException('Invalid request', 400);
        }

        $image = new Image($imageName);
        $image->width = $this->request->query->get('width');
        $image->height = $this->request->query->get('height');

        try {
            $processedImage = $this->imageService->$imageAction($image);
        } catch (ImageModificationException $e) {
            throw new HttpException($e->getMessage(), 500);
        }

        return new RedirectResponse($processedImage->modifiedName);
    }
}
