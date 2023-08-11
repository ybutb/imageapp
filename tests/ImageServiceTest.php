<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Exception\ApiException;
use App\Model\Image;
use App\Service\ImageService;
use Imagick;
use ImagickException;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase
{
    private const INITIAL_WIDTH = 100;
    private const INITIAL_HEIGHT = 100;

    public function testCropSuccess()
    {
        $service = $this->getImageServiceForCropSuccess();

        $image = new Image('test.jpg');
        $image->width = self::INITIAL_WIDTH - 10;
        $image->height = self::INITIAL_HEIGHT - 10;

        $expectedModifiedName = 'crop_' . $image->width . '_' . $image->height . '_test.jpg';

        $service->crop($image);

        $this->assertEquals($image->modifiedName, $expectedModifiedName);
    }

    public function testCropErrorNotExists()
    {
        $service = $this->createPartialMock(ImageService::class, ['getImagick','isOriginalImageExists']);
        $imageName = 'test.jpg';
        $widthToChange = self::INITIAL_WIDTH - 10;
        $heightToChange = self::INITIAL_HEIGHT - 10;

        $service->expects($this->once())
            ->method('isOriginalImageExists')
            ->willThrowException(new ApiException(
                'Failed to find the image: ' . 'crop_' . $widthToChange . '_' . $heightToChange . '_' . $imageName
            ));

        $service->expects($this->never())
            ->method('getImagick');

        $image = new Image($imageName);
        $image->width = $widthToChange;
        $image->height = $heightToChange;

        $this->expectException(ApiException::class);

        $service->crop($image);
    }

    public function testCropErrorMissingParams()
    {
        $service = $this->createPartialMock(ImageService::class, ['getImagick', 'isOriginalImageExists']);
        $imagickMock = $this->createMock(Imagick::class);

        $service->expects($this->any())
            ->method('isOriginalImageExists')
            ->with()
            ->willReturn(true);

        $service->expects($this->any())
            ->method('getImagick')
            ->with()
            ->willReturn($imagickMock);

        $image = new Image('test.jpg');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Width or height for a crop operation is not defined.');
        $this->expectExceptionCode(500);

        $service->crop($image);
    }

    /**
     * @dataProvider imagickErrorsHandlingProvider
     */
    public function testCropImagickErrorsHandling(string $imagickMethodName)
    {
        $imagickMock = $this->createPartialMock(Imagick::class, [$imagickMethodName]);

        $imagickMock->expects($this->any())
            ->method($imagickMethodName)
            ->with()
            ->willThrowException(new ImagickException('test exception'));

        $service = $this->createPartialMock(ImageService::class, ['getImagick', 'isOriginalImageExists']);

        $service->expects($this->once())
            ->method('isOriginalImageExists')
            ->with()
            ->willReturn(true);

        $service->expects($this->once())
            ->method('getImagick')
            ->with()
            ->willReturn($imagickMock);

        $image = new Image('test.jpg');
        $image->width = self::INITIAL_WIDTH + 10;
        $image->height = self::INITIAL_HEIGHT + 10;

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);

        $service->crop($image);
    }

    /**
     * @dataProvider successResizeDataProvider
     */
    public function testResizeSuccess(int $width, int $height) {

        $service = $this->getImageServiceForResizeSuccess();

        $image = new Image('test.jpg');
        $image->width = $width;
        $image->height = $height;

        $service->resize($image);

        $expectedModifiedName = 'resize_' . $image->width . '_' . $image->height . '_test.jpg';;
        $this->assertEquals($image->modifiedName, $expectedModifiedName);
    }

    public function testResizeErrorNotExists() {
        $image = new Image('test.jpg');
        $image->width = self::INITIAL_WIDTH - 10;
        $image->height = self::INITIAL_HEIGHT - 10;

        $service = $this->createPartialMock(ImageService::class, ['getImagick', 'isOriginalImageExists']);

        $service->expects($this->once())
            ->method('isOriginalImageExists')
            ->willThrowException(new ApiException('Failed to find the image: ' . $image->originalName, 404));

        $service->expects($this->never())
            ->method('getImagick');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to find the image: ' . $image->originalName);
        $this->expectExceptionCode(404);

        $service->resize($image);
    }

    public static function successResizeDataProvider(): array
    {
        return [
            'resizeToBigger' => [
                'width' => self::INITIAL_WIDTH + 300,
                'height' => self::INITIAL_HEIGHT + 300
            ],
            'resizeSame' => [
                'width' => self::INITIAL_WIDTH,
                'height' => self::INITIAL_HEIGHT
            ],
            'resizeLess' => [
                'width' => self::INITIAL_WIDTH + 300,
                'height' => self::INITIAL_HEIGHT + 300
            ],
        ];
    }

    public static function imagickErrorsHandlingProvider(): array
    {
        return [
            [
                'writeImage'
            ],
            [
                'cropImage'
            ],
            [
                'setImageFormat'
            ],
        ];
    }

    /**
     * Mocking manipulations with the file system.
     */
    protected function getImageServiceForCropSuccess()
    {
        $imagickMock = $this->createMock(Imagick::class);

        $imagickMock->expects($this->once())
            ->method('writeImage');

        $service = $this->createPartialMock(ImageService::class, ['getImagick', 'isOriginalImageExists']);

        $service->expects($this->once())
            ->method('getImagick')
            ->with()
            ->willReturn($imagickMock);

        $service->expects($this->once())
            ->method('isOriginalImageExists')
            ->with()
            ->willReturn(true);

        return $service;
    }

    /**
     * Mocking manipulations with the file system.
     */
    protected function getImageServiceForResizeSuccess()
    {
        $imagickMock = $this->createMock(Imagick::class);
        $imagickMock->expects($this->atLeastOnce())
            ->method('writeImage');

        $service = $this->createPartialMock(ImageService::class, ['getImagick', 'isOriginalImageExists']);

        $service->expects($this->once())
            ->method('getImagick')
            ->with()
            ->willReturn($imagickMock);

        $service->expects($this->once())
            ->method('isOriginalImageExists')
            ->with()
            ->willReturn(true);

        return $service;
    }
}
