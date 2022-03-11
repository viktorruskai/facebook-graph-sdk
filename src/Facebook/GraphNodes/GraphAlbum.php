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

namespace Facebook\GraphNodes;

use DateTime;

/**
 * Class GraphAlbum
 *
 * @package Facebook
 */
class GraphAlbum extends GraphNode
{
    /**
     * @var array Maps object key names to Graph object types.
     */
    protected static array $graphObjectMap = [
        'from' => GraphUser::class,
        'place' => GraphPage::class,
    ];

    /**
     * Returns the ID for the album.
     */
    public function getId(): ?string
    {
        return $this->getField('id');
    }

    /**
     * Returns whether the viewer can upload photos to this album.
     *
     * @noinspection PhpUnused
     */
    public function getCanUpload(): ?bool
    {
        return $this->getField('can_upload');
    }

    /**
     * Returns the number of photos in this album.
     *
     * @noinspection PhpUnused
     */
    public function getCount(): ?int
    {
        return $this->getField('count');
    }

    /**
     * Returns the ID of the album's cover photo.
     *
     * @noinspection PhpUnused
     */
    public function getCoverPhoto(): ?string
    {
        return $this->getField('cover_photo');
    }

    /**
     * Returns the time the album was initially created.
     */
    public function getCreatedTime(): ?DateTime
    {
        return $this->getField('created_time');
    }

    /**
     * Returns the time the album was updated.
     */
    public function getUpdatedTime(): ?DateTime
    {
        return $this->getField('updated_time');
    }

    /**
     * Returns the description of the album.
     *
     * @noinspection PhpUnused
     */
    public function getDescription(): ?string
    {
        return $this->getField('description');
    }

    /**
     * Returns profile that created the album.
     */
    public function getFrom(): ?GraphUser
    {
        return $this->getField('from');
    }

    /**
     * Returns profile that created the album.
     */
    public function getPlace(): ?GraphPage
    {
        return $this->getField('place');
    }

    /**
     * Returns a link to this album on Facebook.
     *
     * @noinspection PhpUnused
     */
    public function getLink(): ?string
    {
        return $this->getField('link');
    }

    /**
     * Returns the textual location of the album.
     *
     * @noinspection PhpUnused
     */
    public function getLocation(): ?string
    {
        return $this->getField('location');
    }

    /**
     * Returns the title of the album.
     */
    public function getName(): ?string
    {
        return $this->getField('name');
    }

    /**
     * Returns the privacy settings for the album.
     *
     * @noinspection PhpUnused
     */
    public function getPrivacy(): ?string
    {
        return $this->getField('privacy');
    }

    /**
     * Returns the type of the album.
     *
     * enum{ profile, mobile, wall, normal, album }
     */
    public function getType(): ?string
    {
        return $this->getField('type');
    }
}
