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

use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use JsonException;

/**
 * Class FacebookPageTabHelper
 *
 * @package Facebook
 */
class FacebookPageTabHelper extends FacebookCanvasHelper
{
    protected ?array $pageData;

    /**
     * Initialize the helper and process available signed request data.
     *
     * @param FacebookApp $app The FacebookApp entity.
     * @param FacebookClient $client The client to make HTTP requests.
     * @param null $graphVersion The version of Graph to use.
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function __construct(FacebookApp $app, FacebookClient $client, $graphVersion = null)
    {
        parent::__construct($app, $client, $graphVersion);

        if (!$this->signedRequest) {
            return;
        }

        $this->pageData = $this->signedRequest->get('page');
    }

    /**
     * Returns a value from the page data.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getPageData(string $key, mixed $default = null): mixed
    {
        return $this->pageData[$key] ?? $default;
    }

    /**
     * Returns true if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->getPageData('admin') === true;
    }

    /**
     * Returns the page id if available.
     */
    public function getPageId(): ?string
    {
        return $this->getPageData('id');
    }
}
