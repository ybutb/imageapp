<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\HttpFoundation\Request;

class ImageRequestValidator
{
    public static function validate(Request $request): bool
    {
        if (in_array($request->query->get('action'), ['crop', 'resize'], true)) {
            return false;
        }

        $imageName = $request->query->get('image');

        if (!is_string($imageName)) {
            return false;
        }

        $width = $request->query->get('width');

        if ($width && !is_numeric($width)) {
            return false;
        }

        $height = $request->query->get('height');

        if ($height && !is_int($height)) {
            return false;
        }

        if ($height && $height > 5000 || $width && $width > 5000) {
            return false;
        }

        return true;
    }
}