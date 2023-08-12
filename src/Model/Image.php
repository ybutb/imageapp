<?php

declare(strict_types=1);

namespace App\Model;
class Image
{
    public string $modifiedName;
    public ?int $width = null;
    public ?int $height = null;

    public function __construct(public string $originalName, public string $modificationType)
    {
    }

    public function getExtension(): string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }
}