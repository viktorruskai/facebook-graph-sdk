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

use Facebook\Exceptions\FacebookSDKException;
use Facebook\HttpClients\FacebookCurl;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Mockery as m;

class FacebookCurlHttpClientTest extends AbstractTestHttpClient
{
    protected FacebookCurl $curlMock;
    protected FacebookCurlHttpClient $curlClient;

    public const CURL_VERSION_STABLE = 0x072400;
    public const CURL_VERSION_BUGGY = 0x071400;

    protected function setUp(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL must be installed to test cURL client handler.');
        }
        $this->curlMock = m::mock(FacebookCurl::class);
        $this->curlClient = new FacebookCurlHttpClient($this->curlMock);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testCanOpenGetCurlConnection(): void
    {
        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->with(m::on(static function ($arg) {

                // array_diff() will sometimes trigger error on child-arrays
                if (['X-Foo-Header: X-Bar'] !== $arg[CURLOPT_HTTPHEADER]) {
                    return false;
                }
                unset($arg[CURLOPT_HTTPHEADER]);

                $caInfo = array_diff($arg, [
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_URL => 'http://foo.com',
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 123,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
                    return false;
                }

                return true;
            }))
            ->once()
            ->andReturn(null);

        $this->curlClient->openConnection('http://foo.com', 'GET', 'foo_body', ['X-Foo-Header' => 'X-Bar'], 123);
        $this->assertTrue(true);
    }

    public function testCanOpenCurlConnectionWithPostBody(): void
    {
        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->with(m::on(static function ($arg) {

                // array_diff() will sometimes trigger error on child-arrays
                if ([] !== $arg[CURLOPT_HTTPHEADER]) {
                    return false;
                }
                unset($arg[CURLOPT_HTTPHEADER]);

                $caInfo = array_diff($arg, [
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_URL => 'http://bar.com',
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_POSTFIELDS => 'baz=bar',
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo[CURLOPT_CAINFO])) {
                    return false;
                }

                return true;
            }))
            ->once()
            ->andReturn(null);

        $this->curlClient->openConnection('http://bar.com', 'POST', 'baz=bar', [], 60);
        $this->assertTrue(true);
    }

    public function testCanCloseConnection(): void
    {
        $this->curlMock
            ->shouldReceive('close')
            ->once()
            ->andReturn(null);

        $this->curlClient->closeConnection();
        $this->assertTrue(true);
    }

    public function testIsolatesTheHeaderAndBody(): void
    {
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($this->fakeRawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        [$rawHeader, $rawBody] = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeader, trim($this->fakeRawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeaders(): void
    {
        $rawHeader = $this->fakeRawProxyHeader . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        [$rawHeaders, $rawBody] = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeadersWithCurlBug(): void
    {
        $rawHeader = $this->fakeRawProxyHeader . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        [$rawHeaders, $rawBody] = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesProxyHeadersWithCurlBug2(): void
    {
        $rawHeader = $this->fakeRawProxyHeader2 . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        [$rawHeaders, $rawBody] = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    public function testProperlyHandlesRedirectHeaders(): void
    {
        $rawHeader = $this->fakeRawRedirectHeader . $this->fakeRawHeader;
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($rawHeader . $this->fakeRawBody);

        $this->curlClient->sendRequest();
        [$rawHeaders, $rawBody] = $this->curlClient->extractResponseHeadersAndBody();

        $this->assertEquals($rawHeaders, trim($rawHeader));
        $this->assertEquals($rawBody, $this->fakeRawBody);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testCanSendNormalRequest(): void
    {
        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn($this->fakeRawHeader . $this->fakeRawBody);
        $this->curlMock
            ->shouldReceive('errno')
            ->once()
            ->andReturn(0);
        $this->curlMock
            ->shouldReceive('close')
            ->once()
            ->andReturn(null);

        $response = $this->curlClient->send('http://foo.com/', 'GET', '', [], 60);

        $this->assertEquals($this->fakeRawBody, $response->getBody());
        $this->assertEquals($this->fakeHeadersAsArray, $response->getHeaders());
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    public function testThrowsExceptionOnClientError(): void
    {
        $this->expectException(FacebookSDKException::class);

        $this->curlMock
            ->shouldReceive('init')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('setoptArray')
            ->once()
            ->andReturn(null);
        $this->curlMock
            ->shouldReceive('exec')
            ->once()
            ->andReturn(false);
        $this->curlMock
            ->shouldReceive('errno')
            ->once()
            ->andReturn(123);
        $this->curlMock
            ->shouldReceive('error')
            ->once()
            ->andReturn('Foo error');

        $this->curlClient->send('http://foo.com/', 'GET', '', [], 60);
    }
}
