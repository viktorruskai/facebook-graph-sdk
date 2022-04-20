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

namespace Facebook;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphAlbum;
use Facebook\GraphNodes\GraphEdge;
use Facebook\GraphNodes\GraphEvent;
use Facebook\GraphNodes\GraphGroup;
use Facebook\GraphNodes\GraphNode;
use Facebook\GraphNodes\GraphNodeFactory;
use Facebook\GraphNodes\GraphPage;
use Facebook\GraphNodes\GraphSessionInfo;
use Facebook\GraphNodes\GraphUser;
use JsonException;

/**
 * Class FacebookResponse
 *
 * @package Facebook
 */
class FacebookResponse
{
    /**
     * @var int|null The HTTP status code response from Graph.
     */
    protected ?int $httpStatusCode;

    /**
     * @var array The headers returned from Graph.
     */
    protected array $headers;

    /**
     * @var string|null The raw body of the response from Graph.
     */
    protected ?string $body;

    /**
     * @var array The decoded body of the Graph response.
     */
    protected array $decodedBody = [];

    /**
     * @var FacebookRequest The original request that returned this response.
     */
    protected FacebookRequest $request;

    /**
     * @var FacebookSDKException The exception thrown by this request.
     */
    protected FacebookSDKException $thrownException;

    /**
     * Creates a new Response entity.
     *
     * @throws JsonException
     */
    public function __construct(FacebookRequest $request, ?string $body = null, int|string|null $httpStatusCode = null, array $headers = [])
    {
        $this->request = $request;
        $this->body = !isset($body) || $body === '' ? null : $body;
        $this->httpStatusCode = isset($httpStatusCode) ? (int)$httpStatusCode : null;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Return the original request that returned this response.
     */
    public function getRequest(): FacebookRequest
    {
        return $this->request;
    }

    /**
     * Return the FacebookApp entity used for this response.
     *
     * @noinspection PhpUnused
     */
    public function getApp(): FacebookApp
    {
        return $this->request->getApp();
    }

    /**
     * Return the access token that was used for this response.
     */
    public function getAccessToken(): ?string
    {
        return $this->request->getAccessToken();
    }

    /**
     * Return the HTTP status code for this response.
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * Return the HTTP headers for this response.
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     */
    public function getDecodedBody(): array
    {
        return $this->decodedBody;
    }

    /**
     * Get the app secret proof that was used for this response.
     */
    public function getAppSecretProof(): ?string
    {
        return $this->request->getAppSecretProof();
    }

    /**
     * Get the ETag associated with the response.
     */
    public function getETag(): ?string
    {
        return $this->headers['ETag'] ?? null;
    }

    /**
     * Get the version of Graph that returned this response.
     *
     * @noinspection PhpUnused
     */
    public function getGraphVersion(): ?string
    {
        return $this->headers['Facebook-API-Version'] ?? null;
    }

    /**
     * Returns true if Graph returned an error message.
     */
    public function isError(): bool
    {
        return isset($this->decodedBody['error']);
    }

    /**
     * Throws the exception.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpUnused
     */
    public function throwException(): void
    {
        throw $this->thrownException;
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    public function makeException(): void
    {
        $this->thrownException = FacebookResponseException::create($this);
    }

    /**
     * Returns the exception that was thrown for this request.
     */
    public function getThrownException(): FacebookSDKException|FacebookResponseException|null
    {
        return $this->thrownException;
    }

    /**
     * Convert the raw response into an array if possible.
     *
     * Graph will return 2 types of responses:
     * - JSON(P)
     *    Most responses from Graph are JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Happens on the `/oauth/access_token` endpoint when exchanging
     *    a short-lived access token for a long-lived access token
     * - And sometimes nothing :/ but that'd be a bug.
     *
     * @throws JsonException
     */
    public function decodeBody(): void
    {
        $decodedBody = $this->body !== null ? json_decode($this->body, true, 512, JSON_THROW_ON_ERROR) : '';

        if ($decodedBody === null) {
            $this->decodedBody = [];
            parse_str($this->body, $decodedBody);
        } elseif (is_bool($decodedBody)) {
            // Backwards compatibility for Graph < 2.1.
            // Mimics 2.1 responses.
            // @TODO Remove this after Graph 2.0 is no longer supported
            $this->decodedBody = ['success' => $decodedBody];
        } elseif (is_numeric($decodedBody)) {
            $this->decodedBody = ['id' => $decodedBody];
        }

        if (!is_array($decodedBody)) {
            $this->decodedBody = [];
        } else if (!$this->isError()) {
            $this->decodedBody = $decodedBody;
        }

        if ($this->isError()) {
            $this->makeException();
        }
    }

    /**
     * Instantiate a new GraphNode from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast to.
     *
     * @throws FacebookSDKException
     */
    public function getGraphNode(string $subclassName = null): GraphNode
    {
        return (new GraphNodeFactory($this))->makeGraphNode($subclassName);
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpUnused
     */
    public function getGraphAlbum(): GraphAlbum
    {
        return (new GraphNodeFactory($this))->makeGraphAlbum();
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpUnused
     */
    public function getGraphPage(): GraphPage
    {
        return (new GraphNodeFactory($this))->makeGraphPage();
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpUnused
     */
    public function getGraphSessionInfo(): GraphSessionInfo
    {
        return (new GraphNodeFactory($this))->makeGraphSessionInfo();
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpUnused
     */
    public function getGraphUser(): GraphUser
    {
        return (new GraphNodeFactory($this))->makeGraphUser();
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpUnused
     */
    public function getGraphEvent(): GraphEvent
    {
        return (new GraphNodeFactory($this))->makeGraphEvent();
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpUnused
     */
    public function getGraphGroup(): GraphGroup
    {
        return (new GraphNodeFactory($this))->makeGraphGroup();
    }

    /**
     * Instantiate a new GraphEdge from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast list items to.
     * @param boolean $auto_prefix Toggle to auto-prefix the subclass name.
     *
     * @throws FacebookSDKException
     */
    public function getGraphEdge(?string $subclassName = null, bool $auto_prefix = true): GraphEdge
    {
        return (new GraphNodeFactory($this))->makeGraphEdge($subclassName, $auto_prefix);
    }
}
