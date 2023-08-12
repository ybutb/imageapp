<?php

namespace App\Tests\Unit;

use App\Controller\ImageController;
use App\Exception\ApiException;
use App\Model\Image;
use App\Service\ImageService;
use App\Service\ImagickFacade;
use App\Tests\BaseRequestTestCase;

class ControllerTest extends BaseRequestTestCase
{
    /**
     * @dataProvider successDataProvider
     */
    public function testIndexSuccess(string $action)
    {
        $imageName = 'test.jpg';
        $width = 100;
        $height = 200;

        $image = new Image($imageName, $action);
        $image->width = $width;
        $image->height = $height;
        $image->modifiedName = $action . '_' . $width . '_' . $height . '_' . $imageName;

        $request = $this->createRequest(['width' => $width, 'height' => $height], '/' . $imageName . '/' . $action);

        $imagickFacadeMock = $this->createMock(ImagickFacade::class);
        $imageServiceMock = $this->getMockBuilder(ImageService::class)
            ->setConstructorArgs([$imagickFacadeMock])
            ->onlyMethods(['isOriginalImageExists'])
            ->getMock();

        $controller = new ImageController($imageServiceMock, $request);
        $response = $controller->index($imageName, $action);

        $this->assertEquals($response->getTargetUrl(), '/' . $image->modifiedName);
        $this->assertTrue($response->isRedirect('/' . $image->modifiedName));
    }

    public static function successDataProvider()
    {
        return [
            'crop' => ['crop'],
            'resize' => ['resize'],
        ];
    }

    public function testIndexErrorWrongAction()
    {
        $imageServiceMock = $this->createMock(ImageService::class);

        $wrongAction = 'wrongAction';
        $request = $this->createRequest(['width' => '100', 'height' => '200'], '/test.jpg/' . $wrongAction);
        $controller = new ImageController($imageServiceMock, $request);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid image action');
        $this->expectExceptionCode(400);

        $controller->index('test.jpg', $wrongAction);
    }

    /**
     * @dataProvider validationErrorDataProvider
     */
    public function testIndexErrorParams(array $params)
    {
        $action = 'crop';
        $path = '/test.jpg/' . $action;
        $request = $this->createRequest($params, $path);

        $controller = new ImageController($this->createMock(ImageService::class), $request);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad request');
        $this->expectExceptionCode(400);

        $controller->index('test.jpg', $action);
    }

    public static function validationErrorDataProvider(): array
    {
        return [
            'no params' => [[]],
            'no width' => [[
                'height' => 100,
            ]],
            'no height' => [[
                'width' => 100,
            ]],
            'wrong width and height 1' => [[
                'width' => 'wrong',
                'height' => 'wrong',
            ]],
            'wrong width and height 2' => [[
                'width' => null,
                'height' => null,
            ]],
            'width more than max' => [[
                'width' => 10000,
                'height' => 100,
            ]],
            'height more than max' => [[
                'width' => 100,
                'height' => 10000,
            ]],
        ];
    }

    public function testShowSuccess()
    {
        $path = '/show';
        $request = $this->createRequest([], $path);

        $controller = new ImageController(new ImageService($this->createMock(ImagickFacade::class)), $request);

        $response = $controller->show();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->getContent());
        $this->assertStringContainsString('resized_dog.webp', $response->getContent());
        $this->assertStringContainsString('cropped_dog.webp', $response->getContent());
    }
}