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

use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookSDKException;
use JetBrains\PhpStorm\Pure;
use Serializable;

class FacebookApp implements Serializable
{
    /**
     * @var string The app ID.
     */
    protected string $id;

    /**
     * @var string The app secret.
     */
    protected string $secret;

    public function __construct(string $id, string $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    /**
     * Returns the app ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the app secret.
     *
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Returns an app access token.
     *
     * @return AccessToken
     */
    public function getAccessToken(): AccessToken
    {
        return new AccessToken($this->id . '|' . $this->secret);
    }

    /**
     * Serializes the FacebookApp entity as a string.
     *
     * @return string
     */
    public function serialize(): string
    {
        return implode('|', [$this->id, $this->secret]);
    }

    /**
     * Unserializes a string as a FacebookApp entity.
     */
    public function unserialize(string $data)
    {
        [$id, $secret] = explode('|', $data);

        $this->__construct($id, $secret);
    }

    #[Pure]
    public function __serialize(): array
    {
        return (array)$this->serialize();
    }

    public function __unserialize(array $data): void
    {
        $this->unserialize($data[0] ?? '');
    }
}
