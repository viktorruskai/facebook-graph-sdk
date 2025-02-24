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

namespace Facebook\Tests\GraphNodes;

use DateTime;
use Exception;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphApplication;
use Facebook\GraphNodes\GraphUser;

class GraphAchievementTest extends AbstractGraphNode
{

    /**
     * @throws FacebookSDKException
     */
    public function testIdIsString(): void
    {
        $dataFromGraph = [
            'id' => '1337'
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $id = $graphNode->getId();

        $this->assertEquals($dataFromGraph['id'], $id);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testTypeIsAlwaysString(): void
    {
        $dataFromGraph = [
            'id' => '1337'
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $type = $graphNode->getType();

        $this->assertEquals('game.achievement', $type);
    }

    /**
     * @throws FacebookSDKException
     * @throws Exception
     */
    public function testNoFeedStoryIsBoolean(): void
    {
        $dataFromGraph = [
            'no_feed_story' => (random_int(0, 1) === 1)
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $isNoFeedStory = $graphNode->isNoFeedStory();

        $this->assertIsBool($isNoFeedStory);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testDatesGetCastToDateTime(): void
    {
        $dataFromGraph = [
            'publish_time' => '2014-07-15T03:54:34+0000'
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $publishTime = $graphNode->getPublishTime();

        $this->assertInstanceOf(DateTime::class, $publishTime);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testFromGetsCastAsGraphUser(): void
    {
        $dataFromGraph = [
            'from' => [
                'id' => '1337',
                'name' => 'Foo McBar'
            ]
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $from = $graphNode->getFrom();

        $this->assertInstanceOf(GraphUser::class, $from);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testApplicationGetsCastAsGraphApplication(): void
    {
        $dataFromGraph = [
            'application' => [
                'id' => '1337'
            ]
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $app = $graphNode->getApplication();

        $this->assertInstanceOf(GraphApplication::class, $app);
    }
}
