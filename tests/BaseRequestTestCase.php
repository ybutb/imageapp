<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BaseRequestTestCase extends TestCase
{
    protected function createRequest(array $params, string $baseUrl): Request
    {
        $url = $baseUrl . '?' . http_build_query($params);

        return new Request(
            $params,
            [],
            [],
            [],
            [],
            ['REQUEST_URI' => $url]
        );
    }
}