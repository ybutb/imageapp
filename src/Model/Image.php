<?php

declare(strict_types=1);

namespace App\Model;
class Image
{
    public string $modifiedName;
    public ?string $width = null;
    public ?string $height = null;

    public function __construct(public string $initialName)
    {
    }
}