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

use Facebook\FacebookApp;
use PHPUnit\Framework\TestCase;

class FacebookAppTest extends TestCase
{
    private FacebookApp $app;

    protected function setUp(): void
    {
        $this->app = new FacebookApp('id', 'secret');
    }

    public function testGetId(): void
    {
        $this->assertEquals('id', $this->app->getId());
    }

    public function testGetSecret(): void
    {
        $this->assertEquals('secret', $this->app->getSecret());
    }

    public function testAnAppAccessTokenCanBeGenerated(): void
    {
        $accessToken = $this->app->getAccessToken();

        $this->assertEquals('id|secret', (string)$accessToken);
    }

    public function testSerialization(): void
    {
        $newApp = unserialize(serialize($this->app));

        $this->assertInstanceOf(FacebookApp::class, $newApp);
        $this->assertEquals('id', $newApp->getId());
        $this->assertEquals('secret', $newApp->getSecret());
    }

    public function testUnserializedIdsWillBeString(): void
    {
        $newApp = unserialize(serialize(new FacebookApp('1', "foo")));

        $this->assertSame('1', $newApp->getId());
    }
}
