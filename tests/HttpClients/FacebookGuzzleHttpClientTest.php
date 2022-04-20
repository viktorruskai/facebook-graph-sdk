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
use Facebook\Http\GraphRawResponse;
use Facebook\HttpClients\FacebookGuzzleHttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery as m;

class FacebookGuzzleHttpClientTest extends AbstractTestHttpClient
{
    protected Client $guzzleMock;
    protected FacebookGuzzleHttpClient $guzzleClient;

    protected function setUp(): void
    {
        $this->guzzleMock = m::mock(Client::class);
        $this->guzzleClient = new FacebookGuzzleHttpClient($this->guzzleMock);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @throws FacebookSDKException
     */
    public function testCanSendNormalRequest(): void
    {
        $request = new Request('GET', 'http://foo.com');

        $response = new Response(200, $this->fakeHeadersAsArray, $this->fakeRawBody);

        $this->guzzleMock
            ->shouldReceive('createRequest')
            ->once()
            ->with('GET', 'http://foo.com/', m::on(static function ($arg) {

                // array_diff_assoc() will sometimes trigger error on child-arrays
                if (['X-foo' => 'bar'] !== $arg['headers']) {
                    return false;
                }
                unset($arg['headers']);

                $caInfo = array_diff_assoc($arg, [
                    'body' => 'foo_body',
                    'timeout' => 123,
                    'connect_timeout' => 10,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['verify'])) {
                    return false;
                }

                return true;
            }))
            ->andReturn($request);
        $this->guzzleMock
            ->shouldReceive('send')
            ->once()
            ->with($request)
            ->andReturn($response);

        $response = $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', ['X-foo' => 'bar'], 123);

        $this->assertInstanceOf(GraphRawResponse::class, $response);
        $this->assertEquals($this->fakeRawBody, $response->getBody());
        $this->assertEquals($this->fakeHeadersAsArray, $response->getHeaders());
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

    public function testThrowsExceptionOnClientError(): void
    {
        $this->expectException(FacebookSDKException::class);
        $request = new Request('GET', 'http://foo.com');

        $this->guzzleMock
            ->shouldReceive('createRequest')
            ->once()
            ->with('GET', 'http://foo.com/', m::on(static function ($arg) {

                // array_diff_assoc() will sometimes trigger error on child-arrays
                if ([] !== $arg['headers']) {
                    return false;
                }
                unset($arg['headers']);

                $caInfo = array_diff_assoc($arg, [
                    'body' => 'foo_body',
                    'timeout' => 60,
                    'connect_timeout' => 10,
                ]);

                if (count($caInfo) !== 1) {
                    return false;
                }

                if (1 !== preg_match('/.+\/certs\/DigiCertHighAssuranceEVRootCA\.pem$/', $caInfo['verify'])) {
                    return false;
                }

                return true;
            }))
            ->andReturn($request);
        $this->guzzleMock
            ->shouldReceive('send')
            ->once()
            ->with($request)
            ->andThrow(new RequestException('Foo', $request));

        $this->guzzleClient->send('http://foo.com/', 'GET', 'foo_body', [], 60);
    }
}
