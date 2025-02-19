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
use Facebook\FacebookApp;
use Facebook\SignedRequest;
use JsonException;
use PHPUnit\Framework\TestCase;

class SignedRequestTest extends TestCase
{
    protected FacebookApp $app;
    protected string $rawSignature = 'U0_O1MqqNKUt32633zAkdd2Ce-jGVgRgJeRauyx_zC8=';
    protected string $rawPayload = 'eyJvYXV0aF90b2tlbiI6ImZvb190b2tlbiIsImFsZ29yaXRobSI6IkhNQUMtU0hBMjU2IiwiaXNzdWVkX2F0IjozMjEsImNvZGUiOiJmb29fY29kZSIsInN0YXRlIjoiZm9vX3N0YXRlIiwidXNlcl9pZCI6MTIzLCJmb28iOiJiYXIifQ==';

    protected array $payloadData = [
        'oauth_token' => 'foo_token',
        'algorithm' => 'HMAC-SHA256',
        'issued_at' => 321,
        'code' => 'foo_code',
        'state' => 'foo_state',
        'user_id' => 123,
        'foo' => 'bar',
    ];

    protected function setUp(): void
    {
        $this->app = new FacebookApp('123', 'foo_app_secret');
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testAValidSignedRequestCanBeCreated(): void
    {
        $sr = new SignedRequest($this->app);
        $rawSignedRequest = $sr->make($this->payloadData);

        $srTwo = new SignedRequest($this->app, $rawSignedRequest);
        $payload = $srTwo->getPayload();

        $expectedRawSignedRequest = $this->rawSignature . '.' . $this->rawPayload;
        $this->assertEquals($expectedRawSignedRequest, $rawSignedRequest);
        $this->assertEquals($this->payloadData, $payload);
    }

    /**
     *
     * @throws JsonException
     */
    public function testInvalidSignedRequestsWillFailFormattingValidation(): void
    {
        $this->expectException(FacebookSDKException::class);

        new SignedRequest($this->app, 'invalid_signed_request');
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testBase64EncodingIsUrlSafe(): void
    {
        $sr = new SignedRequest($this->app);
        $encodedData = $sr->base64UrlEncode('aijkoprstADIJKLOPQTUVX1256!)]-:;"<>?.|~');

        $this->assertEquals('YWlqa29wcnN0QURJSktMT1BRVFVWWDEyNTYhKV0tOjsiPD4_Lnx-', $encodedData);
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testAUrlSafeBase64EncodedStringCanBeDecoded(): void
    {
        $sr = new SignedRequest($this->app);
        $decodedData = $sr->base64UrlDecode('YWlqa29wcnN0QURJSktMT1BRVFVWWDEyNTYhKV0tOjsiPD4/Lnx+');

        $this->assertEquals('aijkoprstADIJKLOPQTUVX1256!)]-:;"<>?.|~', $decodedData);
    }

    /**
     *
     * @throws JsonException
     */
    public function testAnImproperlyEncodedSignatureWillThrowAnException(): void
    {
        $this->expectException(FacebookSDKException::class);

        new SignedRequest($this->app, 'foo_sig.' . $this->rawPayload);
    }

    /**
     *
     * @throws JsonException
     */
    public function testAnImproperlyEncodedPayloadWillThrowAnException(): void
    {
        $this->expectException(FacebookSDKException::class);

        new SignedRequest($this->app, $this->rawSignature . '.foo_payload');
    }

    /**
     * @throws JsonException
     */
    public function testNonApprovedAlgorithmsWillThrowAnException(): void
    {
        $this->expectException(FacebookSDKException::class);

        $signedRequestData = $this->payloadData;
        $signedRequestData['algorithm'] = 'FOO-ALGORITHM';

        $sr = new SignedRequest($this->app);
        $rawSignedRequest = $sr->make($signedRequestData);

        new SignedRequest($this->app, $rawSignedRequest);
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testAsRawSignedRequestCanBeValidatedAndDecoded(): void
    {
        $rawSignedRequest = $this->rawSignature . '.' . $this->rawPayload;
        $sr = new SignedRequest($this->app, $rawSignedRequest);

        $this->assertEquals($this->payloadData, $sr->getPayload());
    }

    /**
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function testARawSignedRequestCanBeValidatedAndDecoded(): void
    {
        $rawSignedRequest = $this->rawSignature . '.' . $this->rawPayload;
        $sr = new SignedRequest($this->app, $rawSignedRequest);

        $this->assertEquals($sr->getPayload(), $this->payloadData);
        $this->assertEquals($sr->getRawSignedRequest(), $rawSignedRequest);
        $this->assertEquals(123, $sr->getUserId());
        $this->assertTrue($sr->hasOAuthData());
    }
}
