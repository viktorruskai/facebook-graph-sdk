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

/**
 * Class GraphUser
 *
 * @package Facebook
 */
class GraphUser extends GraphNode
{
    /**
     * @var array Maps object key names to Graph object types.
     */
    protected static array $graphObjectMap = [
        'hometown' => GraphPage::class,
        'location' => GraphPage::class,
        'significant_other' => GraphUser::class,
        'picture' => GraphPicture::class,
    ];

    /**
     * Returns the ID for the user as a string if present.
     */
    public function getId(): ?string
    {
        return $this->getField('id');
    }

    /**
     * Returns the name for the user as a string if present.
     */
    public function getName(): ?string
    {
        return $this->getField('name');
    }

    /**
     * Returns the first name for the user as a string if present.
     *
     * @noinspection PhpUnused
     */
    public function getFirstName(): ?string
    {
        return $this->getField('first_name');
    }

    /**
     * Returns the middle name for the user as a string if present.
     *
     * @noinspection PhpUnused
     */
    public function getMiddleName(): ?string
    {
        return $this->getField('middle_name');
    }

    /**
     * Returns the last name for the user as a string if present.
     *
     * @noinspection PhpUnused
     */
    public function getLastName(): ?string
    {
        return $this->getField('last_name');
    }

    /**
     * Returns the email for the user as a string if present.
     *
     * @noinspection PhpUnused
     */
    public function getEmail(): ?string
    {
        return $this->getField('email');
    }

    /**
     * Returns the gender for the user as a string if present.
     *
     * @noinspection PhpUnused
     */
    public function getGender(): ?string
    {
        return $this->getField('gender');
    }

    /**
     * Returns the Facebook URL for the user as a string if available.
     *
     * @noinspection PhpUnused
     */
    public function getLink(): ?string
    {
        return $this->getField('link');
    }

    /**
     * Returns the users birthday, if available.
     *
     * @noinspection PhpUnused
     */
    public function getBirthday(): ?Birthday
    {
        return $this->getField('birthday');
    }

    /**
     * Returns the current location of the user as a GraphPage.
     */
    public function getLocation(): ?GraphPage
    {
        return $this->getField('location');
    }

    /**
     * Returns the current location of the user as a GraphPage.
     */
    public function getHometown(): ?GraphPage
    {
        return $this->getField('hometown');
    }

    /**
     * Returns the current location of the user as a GraphUser.
     */
    public function getSignificantOther(): ?GraphUser
    {
        return $this->getField('significant_other');
    }

    /**
     * Returns the picture of the user as a GraphPicture
     */
    public function getPicture(): ?GraphPicture
    {
        return $this->getField('picture');
    }
}
