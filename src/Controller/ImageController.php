<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Model\Image;
use App\Service\ImageService;
use App\Validator\ImageRequestValidator;
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
     * @throws ApiException
     */
    public function index(string $imageName, string $imageAction): Response
    {
        ImageRequestValidator::validate($this->request);

        $image = new Image($imageName, $imageAction);
        $image->width = (int)$this->request->query->get('width');
        $image->height = (int)$this->request->query->get('height');

        $this->imageService->modify($image);

        return new RedirectResponse('/' . $image->modifiedName);
    }

    public function show(): Response
    {
        $data = ['imagePaths' => $this->imageService->getSampleImagesPaths()];

        return $this->render('index', $data);
    }

    private function render(string $view, array $params): Response
    {
        ob_start();

        extract($params);
        include __DIR__ . '/../templates/' . $view . '.html';
        $content = ob_get_clean();

        return new Response($content);
    }
}
