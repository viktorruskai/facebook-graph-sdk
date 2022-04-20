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

use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphNodeFactory;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Facebook\GraphNodes\GraphCoverPhoto;
use Facebook\GraphNodes\GraphPage;
use Facebook\GraphNodes\GraphPicture;
use Facebook\GraphNodes\GraphGroup;

class GraphEventTest extends TestCase
{
    protected FacebookResponse $responseMock;

    protected function setUp(): void
    {
        $this->responseMock = m::mock(FacebookResponse::class);
    }

    /**
     * @throws FacebookSDKException
     */
    public function testCoverGetsCastAsGraphCoverPhoto(): void
    {
        $dataFromGraph = [
            'cover' => ['id' => '1337']
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);
        $graphObject = $factory->makeGraphEvent();

        $cover = $graphObject->getCover();
        $this->assertInstanceOf(GraphCoverPhoto::class, $cover);
        m::close();
    }

    /**
     * @throws FacebookSDKException
     */
    public function testPlaceGetsCastAsGraphPage(): void
    {
        $dataFromGraph = [
            'place' => ['id' => '1337']
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);
        $graphObject = $factory->makeGraphEvent();

        $place = $graphObject->getPlace();
        $this->assertInstanceOf(GraphPage::class, $place);
        m::close();
    }

    /**
     * @throws FacebookSDKException
     */
    public function testPictureGetsCastAsGraphPicture(): void
    {
        $dataFromGraph = [
            'picture' => ['id' => '1337']
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);
        $graphObject = $factory->makeGraphEvent();

        $picture = $graphObject->getPicture();
        $this->assertInstanceOf(GraphPicture::class, $picture);
        m::close();
    }

    /**
     * @throws FacebookSDKException
     */
    public function testParentGroupGetsCastAsGraphGroup(): void
    {
        $dataFromGraph = [
            'parent_group' => ['id' => '1337']
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);
        $graphObject = $factory->makeGraphEvent();

        $parentGroup = $graphObject->getParentGroup();
        $this->assertInstanceOf(GraphGroup::class, $parentGroup);
        m::close();
    }
}
