<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\ApiException;
use Symfony\Component\HttpFoundation\Request;

class ImageRequestValidator
{
    /**
     * @throws ApiException
     */
    public static function validate(Request $request): void
    {
        $pathInfoArray = explode('/', $request->getPathInfo());
        $imageAction = array_pop($pathInfoArray);

        if (!in_array($imageAction, ['crop', 'resize'], true)) {
            throw new ApiException('Invalid image action', 400);
        }

        $imageName = array_pop($pathInfoArray);

        if (!is_string($imageName)) {
            throw new ApiException('Bad request', 400);
        }

        $width = $request->query->get('width');

        if (!is_numeric($width)) {
            throw new ApiException('Bad request', 400);
        }

        $height = $request->query->get('height');

        if (!is_numeric($height)) {
            throw new ApiException('Bad request', 400);
        }

        if ((int)$height > 5000 || (int)$width > 5000) {
            throw new ApiException('Bad request', 400);
        }
    }
}