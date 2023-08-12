<?php

namespace App\Tests\Unit;

use App\Model\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testPathInfo()
    {
        $image = new Image('test.jpg', 'resize');

        $this->assertEquals($image->getExtension(), 'jpg');
    }
}