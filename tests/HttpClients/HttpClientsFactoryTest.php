<?php
declare(strict_types=1);

/**
 * Copyright 2017 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

namespace Facebook\Tests\HttpClients;

use Exception;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookGuzzleHttpClient;
use Facebook\HttpClients\FacebookHttpClientInterface;
use Facebook\HttpClients\FacebookStreamHttpClient;
use Facebook\HttpClients\HttpClientsFactory;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class HttpClientsFactoryTest extends TestCase
{
    public const COMMON_NAMESPACE = 'Facebook\HttpClients\\';
    public const COMMON_INTERFACE = FacebookHttpClientInterface::class;

    /**
     * @dataProvider httpClientsProvider
     * @throws Exception
     */
    public function testCreateHttpClient(mixed $handler, string $expected): void
    {
        $httpClient = HttpClientsFactory::createHttpClient($handler);

        $this->assertInstanceOf(self::COMMON_INTERFACE, $httpClient);
    }

    public function httpClientsProvider(): array
    {
        $clients = [
            ['guzzle', self::COMMON_NAMESPACE . 'FacebookGuzzleHttpClient'],
            ['stream', self::COMMON_NAMESPACE . 'FacebookStreamHttpClient'],
            [new Client(), self::COMMON_NAMESPACE . 'FacebookGuzzleHttpClient'],
            [new FacebookGuzzleHttpClient(), self::COMMON_NAMESPACE . 'FacebookGuzzleHttpClient'],
            [new FacebookStreamHttpClient(), self::COMMON_NAMESPACE . 'FacebookStreamHttpClient'],
            [null, self::COMMON_INTERFACE],
        ];
        if (extension_loaded('curl')) {
            $clients[] = ['curl', self::COMMON_NAMESPACE . 'FacebookCurlHttpClient'];
            $clients[] = [new FacebookCurlHttpClient(), self::COMMON_NAMESPACE . 'FacebookCurlHttpClient'];
        }

        return $clients;
    }
}
