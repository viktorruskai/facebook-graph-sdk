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

namespace Facebook\Tests;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookBatchRequest;
use Facebook\FacebookRequest;
use Facebook\FileUpload\FacebookFile;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;

class FacebookBatchRequestTest extends TestCase
{

    private FacebookApp $app;

    protected function setUp(): void
    {
        $this->app = new FacebookApp('123', 'foo_secret');
    }

    /**
     * @throws FacebookSDKException
     */
    public function testABatchRequestWillInstantiateWithTheProperProperties(): void
    {
        $batchRequest = new FacebookBatchRequest($this->app, [], 'foo_token', 'v0.1337');

        $this->assertSame($this->app, $batchRequest->getApp());
        $this->assertEquals('foo_token', $batchRequest->getAccessToken());
        $this->assertEquals('POST', $batchRequest->getMethod());
        $this->assertEquals('', $batchRequest->getEndpoint());
        $this->assertEquals('v0.1337', $batchRequest->getGraphVersion());
    }

    /**
     * @throws FacebookSDKException
     */
    public function testEmptyRequestWillFallbackToBatchDefaults(): void
    {
        $request = new FacebookRequest();

        $this->createBatchRequest()->addFallbackDefaults($request);

        $this->assertRequestContainsAppAndToken($request, $this->app, 'foo_token');
    }

    /**
     * @throws FacebookSDKException
     */
    public function testRequestWithTokenOnlyWillFallbackToBatchDefaults(): void
    {
        $request = new FacebookRequest(null, 'bar_token');

        $this->createBatchRequest()->addFallbackDefaults($request);

        $this->assertRequestContainsAppAndToken($request, $this->app, 'bar_token');
    }

    /**
     * @throws FacebookSDKException
     */
    public function testRequestWithAppOnlyWillFallbackToBatchDefaults(): void
    {
        $customApp = new FacebookApp('1337', 'bar_secret');
        $request = new FacebookRequest($customApp);

        $this->createBatchRequest()->addFallbackDefaults($request);

        $this->assertRequestContainsAppAndToken($request, $customApp, 'foo_token');
    }

    public function testWillThrowWhenNoThereIsNoAppFallback(): void
    {
        $this->expectException(FacebookSDKException::class);
        $batchRequest = new FacebookBatchRequest();

        $batchRequest->addFallbackDefaults(new FacebookRequest(null, 'foo_token'));
    }

    public function testWillThrowWhenNoThereIsNoAccessTokenFallback(): void
    {
        $this->expectException(FacebookSDKException::class);
        $request = new FacebookBatchRequest();

        $request->addFallbackDefaults(new FacebookRequest($this->app));
    }

    /**
     * @throws FacebookSDKException
     */
    public function testAnInvalidTypeGivenToAddWillThrow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new FacebookBatchRequest();

        $request->add('foo');
    }

    /**
     * @throws FacebookSDKException
     */
    public function testAddingRequestsWillBeFormattedInAnArrayProperly(): void
    {
        $requests = [
            null => new FacebookRequest(null, null, 'GET', '/foo'),
            'my-second-request' => new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']),
            'my-third-request' => new FacebookRequest(null, null, 'DELETE', '/baz')
        ];

        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($requests[null]);
        $batchRequest->add($requests['my-second-request'], 'my-second-request');
        $batchRequest->add($requests['my-third-request'], 'my-third-request');

        $formattedRequests = $batchRequest->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testANumericArrayOfRequestsCanBeAdded(): void
    {
        $requests = [
            new FacebookRequest(null, null, 'GET', '/foo'),
            new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']),
            new FacebookRequest(null, null, 'DELETE', '/baz'),
        ];

        $formattedRequests = $this->createBatchRequestWithRequests($requests)->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testAnAssociativeArrayOfRequestsCanBeAdded(): void
    {
        $requests = [
            'req-one' => new FacebookRequest(null, null, 'GET', '/foo'),
            'req-two' => new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']),
            'req-three' => new FacebookRequest(null, null, 'DELETE', '/baz'),
        ];

        $formattedRequests = $this->createBatchRequestWithRequests($requests)->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testRequestsCanBeInjectedIntoConstructor(): void
    {
        $requests = [
            new FacebookRequest(null, null, 'GET', '/foo'),
            new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']),
            new FacebookRequest(null, null, 'DELETE', '/baz'),
        ];

        $batchRequest = new FacebookBatchRequest($this->app, $requests, 'foo_token');
        $formattedRequests = $batchRequest->getRequests();

        $this->assertRequestsMatch($requests, $formattedRequests);
    }

    public function testAZeroRequestCountWithThrow(): void
    {
        $this->expectException(FacebookSDKException::class);

        $batchRequest = new FacebookBatchRequest($this->app, [], 'foo_token');

        $batchRequest->validateBatchRequestCount();
    }

    public function testMoreThanFiftyRequestsWillThrow(): void
    {
        $this->expectException(FacebookSDKException::class);

        $batchRequest = $this->createBatchRequest();

        $this->createAndAppendRequestsTo($batchRequest, 51);

        $batchRequest->validateBatchRequestCount();
    }

    /**
     * @throws FacebookSDKException
     */
    public function testLessOrEqualThanFiftyRequestsWillNotThrow(): void
    {
        $batchRequest = $this->createBatchRequest();

        $this->createAndAppendRequestsTo($batchRequest, 50);

        $batchRequest->validateBatchRequestCount();
        $this->assertTrue(true);
    }

    /**
     * @dataProvider requestsAndExpectedResponsesProvider
     * @throws FacebookSDKException
     */
    public function testBatchRequestEntitiesProperlyGetConvertedToAnArray($request, $expectedArray): void
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($request, 'foo_name');

        $requests = $batchRequest->getRequests();
        $batchRequestArray = $batchRequest->requestEntityToBatchArray($requests[0]['request'], $requests[0]['name']);

        $this->assertEquals($expectedArray, $batchRequestArray);
    }

    /**
     * @throws FacebookSDKException
     */
    public function requestsAndExpectedResponsesProvider(): array
    {
        $headers = $this->defaultHeaders();
        $apiVersion = Facebook::DEFAULT_GRAPH_VERSION;

        return [
            [
                new FacebookRequest(null, null, 'GET', '/foo', ['foo' => 'bar']),
                [
                    'headers' => $headers,
                    'method' => 'GET',
                    'relative_url' => '/' . $apiVersion . '/foo?foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
                    'name' => 'foo_name',
                ],
            ],
            [
                new FacebookRequest(null, null, 'POST', '/bar', ['bar' => 'baz']),
                [
                    'headers' => $headers,
                    'method' => 'POST',
                    'relative_url' => '/' . $apiVersion . '/bar',
                    'body' => 'bar=baz&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
                    'name' => 'foo_name',
                ],
            ],
            [
                new FacebookRequest(null, null, 'DELETE', '/bar'),
                [
                    'headers' => $headers,
                    'method' => 'DELETE',
                    'relative_url' => '/' . $apiVersion . '/bar?access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
                    'name' => 'foo_name',
                ],
            ],
        ];
    }

    /**
     * @throws FacebookSDKException
     */
    public function testBatchRequestsWithFilesGetConvertedToAnArray(): void
    {
        $request = new FacebookRequest(null, null, 'POST', '/bar', [
            'message' => 'foobar',
            'source' => new FacebookFile(__DIR__ . '/foo.txt'),
        ]);

        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($request, 'foo_name');

        $requests = $batchRequest->getRequests();

        $attachedFiles = $requests[0]['attached_files'];

        $batchRequestArray = $batchRequest->requestEntityToBatchArray(
            $requests[0]['request'],
            $requests[0]['name'],
            $attachedFiles
        );

        $this->assertEquals([
            'headers' => $this->defaultHeaders(),
            'method' => 'POST',
            'relative_url' => '/' . Facebook::DEFAULT_GRAPH_VERSION . '/bar',
            'body' => 'message=foobar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
            'name' => 'foo_name',
            'attached_files' => $attachedFiles,
        ], $batchRequestArray);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testBatchRequestsWithOptionsGetConvertedToAnArray(): void
    {
        $request = new FacebookRequest(null, null, 'GET', '/bar');
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($request, [
            'name' => 'foo_name',
            'omit_response_on_success' => false,
        ]);

        $requests = $batchRequest->getRequests();

        $options = $requests[0]['options'];
        $options['name'] = $requests[0]['name'];

        $batchRequestArray = $batchRequest->requestEntityToBatchArray($requests[0]['request'], $options);

        $this->assertEquals([
            'headers' => $this->defaultHeaders(),
            'method' => 'GET',
            'relative_url' => '/' . Facebook::DEFAULT_GRAPH_VERSION . '/bar?access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
            'name' => 'foo_name',
            'omit_response_on_success' => false,
        ], $batchRequestArray);
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testPreppingABatchRequestProperlySetsThePostParams(): void
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add(new FacebookRequest(null, 'bar_token', 'GET', '/foo'), 'foo_name');
        $batchRequest->add(new FacebookRequest(null, null, 'POST', '/bar', ['foo' => 'bar']));
        $batchRequest->prepareRequestsForBatch();

        $params = $batchRequest->getParams();

        $expectedHeaders = json_encode($this->defaultHeaders(), JSON_THROW_ON_ERROR);
        $version = Facebook::DEFAULT_GRAPH_VERSION;
        $expectedBatchParams = [
            'batch' => '[{"headers":' . $expectedHeaders . ',"method":"GET","relative_url":"\\/' . $version . '\\/foo?access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd","name":"foo_name"},'
                . '{"headers":' . $expectedHeaders . ',"method":"POST","relative_url":"\\/' . $version . '\\/bar","body":"foo=bar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9"}]',
            'include_headers' => true,
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ];
        $this->assertEquals($expectedBatchParams, $params);
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testPreppingABatchRequestProperlyMovesTheFiles(): void
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add(new FacebookRequest(null, 'bar_token', 'GET', '/foo'), 'foo_name');
        $batchRequest->add(new FacebookRequest(null, null, 'POST', '/me/photos', [
            'message' => 'foobar',
            'source' => new FacebookFile(__DIR__ . '/foo.txt'),
        ]));
        $batchRequest->prepareRequestsForBatch();

        $params = $batchRequest->getParams();
        $files = $batchRequest->getFiles();

        $attachedFiles = implode(',', array_keys($files));

        $expectedHeaders = json_encode($this->defaultHeaders(), JSON_THROW_ON_ERROR);
        $version = Facebook::DEFAULT_GRAPH_VERSION;
        $expectedBatchParams = [
            'batch' => '[{"headers":' . $expectedHeaders . ',"method":"GET","relative_url":"\\/' . $version . '\\/foo?access_token=bar_token&appsecret_proof=2ceec40b7b9fd7d38fff1767b766bcc6b1f9feb378febac4612c156e6a8354bd","name":"foo_name"},'
                . '{"headers":' . $expectedHeaders . ',"method":"POST","relative_url":"\\/' . $version . '\\/me\\/photos","body":"message=foobar&access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9","attached_files":"' . $attachedFiles . '"}]',
            'include_headers' => true,
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ];
        $this->assertEquals($expectedBatchParams, $params);
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testPreppingABatchRequestWithOptionsProperlySetsThePostParams(): void
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add(new FacebookRequest(null, null, 'GET', '/foo'), [
            'name' => 'foo_name',
            'omit_response_on_success' => false,
        ]);

        $batchRequest->prepareRequestsForBatch();
        $params = $batchRequest->getParams();

        $expectedHeaders = json_encode($this->defaultHeaders(), JSON_THROW_ON_ERROR);
        $version = Facebook::DEFAULT_GRAPH_VERSION;

        $expectedBatchParams = [
            'batch' => '[{"headers":' . $expectedHeaders . ',"method":"GET","relative_url":"\\/' . $version . '\\/foo?access_token=foo_token&appsecret_proof=df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9",'
                . '"name":"foo_name","omit_response_on_success":false}]',
            'include_headers' => true,
            'access_token' => 'foo_token',
            'appsecret_proof' => 'df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9',
        ];
        $this->assertEquals($expectedBatchParams, $params);
    }

    private function assertRequestContainsAppAndToken(FacebookRequest $request, FacebookApp $expectedApp, $expectedToken): void
    {
        $app = $request->getApp();
        $token = $request->getAccessToken();

        $this->assertSame($expectedApp, $app);
        $this->assertEquals($expectedToken, $token);
    }

    private function defaultHeaders(): array
    {
        $headers = [];
        foreach (FacebookRequest::getDefaultHeaders() as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        return $headers;
    }

    /**
     * @throws FacebookSDKException
     */
    private function createAndAppendRequestsTo(FacebookBatchRequest $batchRequest, $number): void
    {
        for ($i = 0; $i < $number; $i++) {
            $batchRequest->add(new FacebookRequest());
        }
    }

    /**
     * @throws FacebookSDKException
     */
    private function createBatchRequest(): FacebookBatchRequest
    {
        return new FacebookBatchRequest($this->app, [], 'foo_token');
    }

    /**
     * @throws FacebookSDKException
     */
    private function createBatchRequestWithRequests(array $requests): FacebookBatchRequest
    {
        $batchRequest = $this->createBatchRequest();
        $batchRequest->add($requests);

        return $batchRequest;
    }

    private function assertRequestsMatch($requests, $formattedRequests): void
    {
        $expectedRequests = [];
        foreach ($requests as $name => $request) {
            $expectedRequests[] = [
                'name' => $name,
                'request' => $request,
                'attached_files' => null,
                'options' => [],
            ];
        }
        $this->assertEquals($expectedRequests, $formattedRequests);
    }
}
