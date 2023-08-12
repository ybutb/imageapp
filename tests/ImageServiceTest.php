<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Exception\ApiException;
use App\Model\Image;
use App\Service\ImageService;
use App\Service\ImagickFacade;
use Imagick;
use ImagickException;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase
{
    private const INITIAL_WIDTH = 100;
    private const INITIAL_HEIGHT = 100;

    /**
     * @dataProvider actionsProvider
     */
    public function testModifySuccess(string $action)
    {
        $service = $this->getImageServiceFSMocked();
        $imageName = 'test.jpg';

        $image = new Image($imageName, $action);
        $image->width = self::INITIAL_WIDTH - 10;
        $image->height = self::INITIAL_HEIGHT - 10;

        $expectedModifiedName = $action . '_' . $image->width . '_' . $image->height . '_' . $imageName;

        $service->modify($image);

        $this->assertEquals($image->modifiedName, $expectedModifiedName);
    }

    public static function actionsProvider(): array
    {
        return [
            ['resize'],
            ['crop'],
        ];
    }

    public function testCropErrorNotExists()
    {
        $imageName = 'test.jpg';
        $action = 'crop';
        $widthToChange = self::INITIAL_WIDTH - 10;
        $heightToChange = self::INITIAL_HEIGHT - 10;

        $service = $this->createPartialMock(ImageService::class, ['isOriginalImageExists']);
        $service->expects($this->once())
            ->method('isOriginalImageExists')
            ->willThrowException(new ApiException(
                'Failed to find the image: ' . $action . '_' . $widthToChange . '_' . $heightToChange . '_' . $imageName, 404
            ));

        $image = new Image($imageName, $action);
        $image->width = $widthToChange;
        $image->height = $heightToChange;

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);

        $service->modify($image);
    }

    public function testCropErrorMissingParams()
    {
        $service = $this->getImageServiceFSMocked();
        $action = 'crop';
        $image = new Image('test.jpg', $action);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Width or height for a crop operation is not defined.');
        $this->expectExceptionCode(500);

        $service->modify($image);
    }

    public function testResizeErrorNotExists() {
        $imageName = 'test.jpg';
        $action = 'resize';
        $widthToChange = self::INITIAL_WIDTH - 20;
        $heightToChange = self::INITIAL_HEIGHT + 20;

        $service = $this->createPartialMock(ImageService::class, ['isOriginalImageExists']);
        $service->expects($this->once())
            ->method('isOriginalImageExists')
            ->willThrowException(new ApiException(
                'Failed to find the image: ' . $action . '_' . $widthToChange . '_' . $heightToChange . '_' . $imageName, 404
            ));

        $image = new Image($imageName, $action);
        $image->width = $widthToChange;
        $image->height = $heightToChange;

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);

        $service->modify($image);
    }

    public function testAlreadyModifiedWithSameParametersExists()
    {
        $action = 'resize';
        $imageName = 'test.jpg';
        $widthToChange = self::INITIAL_WIDTH - 20;
        $heightToChange = self::INITIAL_HEIGHT + 20;

        $imagickFacadeMock = $this->createMock(ImagickFacade::class);

        $imagickFacadeMock->expects($this->never())
            ->method($action);

        $service = $this->getMockBuilder(ImageService::class)
            ->setConstructorArgs([$imagickFacadeMock])
            ->onlyMethods(['isOriginalImageExists', 'isModified'])
            ->getMock();

        $service->expects($this->once())
            ->method('isModified')
            ->willReturn(true);

        $image = new Image($imageName, $action);
        $image->width = $widthToChange;
        $image->height = $heightToChange;
        $image->modifiedName = $action . '_' . $widthToChange . '_' . $heightToChange . '_' . $imageName;

        $service->modify($image);

        $this->assertEquals($image->modifiedName, $action . '_' . $widthToChange . '_' . $heightToChange . '_' . $imageName);
    }

    /**
     * Mocking manipulations with the file system only.
     */
    protected function getImageServiceFSMocked()
    {
        $imagickFacadeMock = $this->createMock(ImagickFacade::class);

        return $this->getMockBuilder(ImageService::class)
            ->setConstructorArgs([$imagickFacadeMock])
            ->onlyMethods(['isOriginalImageExists'])
            ->getMock();
    }
}
