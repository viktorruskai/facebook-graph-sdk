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

namespace Facebook\Helpers;

use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\SignedRequest;
use JsonException;

/**
 * Class FacebookSignedRequestFromInputHelper
 *
 * @package Facebook
 */
abstract class FacebookSignedRequestFromInputHelper
{
    /**
     * @var SignedRequest|null The SignedRequest entity.
     */
    protected ?SignedRequest $signedRequest;

    /**
     * @var FacebookApp The FacebookApp entity.
     */
    protected FacebookApp $app;

    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected OAuth2Client $oAuth2Client;

    /**
     * Initialize the helper and process available signed request data.
     *
     * @param FacebookApp $app The FacebookApp entity.
     * @param FacebookClient $client The client to make HTTP requests.
     * @param string|null $graphVersion The version of Graph to use.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function __construct(FacebookApp $app, FacebookClient $client, string $graphVersion = null)
    {
        $this->app = $app;
        $graphVersion = $graphVersion ?: Facebook::DEFAULT_GRAPH_VERSION;
        $this->oAuth2Client = new OAuth2Client($this->app, $client, $graphVersion);

        $this->instantiateSignedRequest();
    }

    /**
     * Instantiates a new SignedRequest entity.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function instantiateSignedRequest(?string $rawSignedRequest = null): void
    {
        $rawSignedRequest = $rawSignedRequest ?: $this->getRawSignedRequest();

        if (!$rawSignedRequest) {
            return;
        }

        $this->signedRequest = new SignedRequest($this->app, $rawSignedRequest);
    }

    /**
     * Returns an AccessToken entity from the signed request.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function getAccessToken(): ?AccessToken
    {
        if ($this->signedRequest && $this->signedRequest->hasOAuthData()) {
            $code = $this->signedRequest->get('code');
            $accessToken = $this->signedRequest->get('oauth_token');

            if ($code && !$accessToken) {
                return $this->oAuth2Client->getAccessTokenFromCode($code);
            }

            $expiresAt = $this->signedRequest->get('expires', 0);

            return new AccessToken($accessToken, $expiresAt);
        }

        return null;
    }

    /**
     * Returns the SignedRequest entity.
     *
     * @noinspection PhpUnused
     */
    public function getSignedRequest(): ?SignedRequest
    {
        return $this->signedRequest;
    }

    /**
     * Returns the user_id if available.
     *
     * @noinspection PhpUnused
     */
    public function getUserId(): ?string
    {
        return $this->signedRequest?->getUserId();
    }

    /**
     * Get raw signed request from input.
     *
     * @return string|null
     */
    abstract public function getRawSignedRequest(): ?string;

    /**
     * Get raw signed request from POST input.
     */
    public function getRawSignedRequestFromPost(): ?string
    {
        return $_POST['signed_request'] ?? null;
    }

    /**
     * Get raw signed request from cookie set from the Javascript SDK.
     */
    public function getRawSignedRequestFromCookie(): ?string
    {
        return $_COOKIE['fbsr_' . $this->app->getId()] ?? null;
    }
}
