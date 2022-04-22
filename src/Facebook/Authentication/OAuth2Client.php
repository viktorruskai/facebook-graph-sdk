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

namespace Facebook\Authentication;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use JsonException;

/**
 * Class OAuth2Client
 *
 * @package Facebook
 */
class OAuth2Client
{
    /**
     * @const string The base authorization URL.
     */
    public const BASE_AUTHORIZATION_URL = 'https://www.facebook.com';

    /**
     * The FacebookApp entity.
     */
    protected FacebookApp $app;

    /**
     * The Facebook client.
     */
    protected FacebookClient $client;

    /**
     * The version of the Graph API to use.
     */
    protected string $graphVersion;

    /**
     * The last request sent to Graph.
     */
    protected ?FacebookRequest $lastRequest;

    public function __construct(FacebookApp $app, FacebookClient $client, ?string $graphVersion = null)
    {
        $this->app = $app;
        $this->client = $client;
        $this->graphVersion = $graphVersion ?: Facebook::DEFAULT_GRAPH_VERSION;
    }

    /**
     * Returns the last FacebookRequest that was sent.
     * Useful for debugging and testing.
     */
    public function getLastRequest(): ?FacebookRequest
    {
        return $this->lastRequest;
    }

    /**
     * Get the metadata associated with the access token.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function debugToken(string|AccessToken $accessToken): AccessTokenMetadata
    {
        $accessToken = $accessToken instanceof AccessToken ? $accessToken->getValue() : $accessToken;
        $params = ['input_token' => $accessToken];

        $this->lastRequest = new FacebookRequest(
            $this->app,
            $this->app->getAccessToken(),
            'GET',
            '/debug_token',
            $params,
            null,
            $this->graphVersion
        );
        $response = $this->client->sendRequest($this->lastRequest);
        $metadata = $response->getDecodedBody();

        return new AccessTokenMetadata($metadata);
    }

    /**
     * Generates an authorization URL to begin the process of authenticating a user.
     */
    public function getAuthorizationUrl(string $redirectUrl, string $state, array $scope = [], array $params = [], string $separator = '&'): string
    {
        $params += [
            'client_id' => $this->app->getId(),
            'state' => $state,
            'response_type' => 'code',
            'sdk' => 'php-sdk-' . Facebook::VERSION,
            'redirect_uri' => $redirectUrl,
            'scope' => implode(',', $scope)
        ];

        return static::BASE_AUTHORIZATION_URL . '/' . $this->graphVersion . '/dialog/oauth?' . http_build_query($params, '', $separator);
    }

    /**
     * Get a valid access token from a code.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function getAccessTokenFromCode(string $code, string $redirectUri = ''): AccessToken
    {
        $params = [
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        return $this->requestAnAccessToken($params);
    }

    /**
     * Exchanges a short-lived access token with a long-lived access token.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function getLongLivedAccessToken(string|AccessToken $accessToken): AccessToken
    {
        $accessToken = $accessToken instanceof AccessToken ? $accessToken->getValue() : $accessToken;
        $params = [
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $accessToken,
        ];

        return $this->requestAnAccessToken($params);
    }

    /**
     * Get a valid code from an access token.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function getCodeFromLongLivedAccessToken(string|AccessToken $accessToken, string $redirectUri = ''): AccessToken|string
    {
        $params = [
            'redirect_uri' => $redirectUri,
        ];

        $response = $this->sendRequestWithClientParams('/oauth/client_code', $params, $accessToken);
        $data = $response->getDecodedBody();

        if (!isset($data['code'])) {
            throw new FacebookSDKException('Code was not returned from Graph.', 401);
        }

        return $data['code'];
    }

    /**
     * Send a request to the OAuth endpoint.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    protected function requestAnAccessToken(array $params): AccessToken
    {
        $response = $this->sendRequestWithClientParams('/oauth/access_token', $params);
        $data = $response->getDecodedBody();

        if (!isset($data['access_token'])) {
            throw new FacebookSDKException('Access token was not returned from Graph.', 401);
        }

        // Graph returns two different key names for expiration time
        // on the same endpoint. Doh! :/
        $expiresAt = 0;
        if (isset($data['expires'])) {
            // For exchanging a short lived token with a long lived token.
            // The expiration time in seconds will be returned as "expires".
            $expiresAt = time() + $data['expires'];
        } elseif (isset($data['expires_in'])) {
            // For exchanging a code for a short lived access token.
            // The expiration time in seconds will be returned as "expires_in".
            // See: https://developers.facebook.com/docs/facebook-login/access-tokens#long-via-code
            $expiresAt = time() + $data['expires_in'];
        }

        return new AccessToken($data['access_token'], $expiresAt);
    }

    /**
     * Send a request to Graph with an app access token.
     *
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     * @throws JsonException
     */
    protected function sendRequestWithClientParams(string $endpoint, array $params, string|AccessToken|null $accessToken = null): FacebookResponse
    {
        $params += $this->getClientParams();

        $accessToken = $accessToken ?: $this->app->getAccessToken();

        $this->lastRequest = new FacebookRequest(
            $this->app,
            $accessToken,
            'GET',
            $endpoint,
            $params,
            null,
            $this->graphVersion
        );

        return $this->client->sendRequest($this->lastRequest);
    }

    /**
     * Returns the client_* params for OAuth requests.
     */
    protected function getClientParams(): array
    {
        return [
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
        ];
    }
}
