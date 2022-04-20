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
use DateTimeInterface;
use Exception;
use JsonException;

/**
 * Class GraphNode
 *
 * @package Facebook
 */
class GraphNode extends Collection
{
    /**
     * @var array Maps object key names to Graph object types.
     */
    protected static array $graphObjectMap = [];

    /**
     * Init this Graph object.
     *
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        parent::__construct($this->castItems($data));
    }

    /**
     * Iterates over an array and detects the types each node
     * should be cast to and returns all the items as an array.
     *
     * @TODO Add auto-casting to AccessToken entities.
     *
     * @throws Exception
     */
    public function castItems(array $data): array
    {
        $items = [];

        foreach ($data as $k => $v) {
            if ($this->shouldCastAsDateTime((string)$k)
                && (is_numeric($v)
                    || $this->isIso8601DateString($v))
            ) {
                $items[$k] = $this->castToDateTime($v);
            } elseif ($k === 'birthday') {
                $items[$k] = $this->castToBirthday($v);
            } else {
                $items[$k] = $v;
            }
        }

        return $items;
    }

    /**
     * Determines if a value from Graph should be cast to DateTime.
     */
    public function shouldCastAsDateTime(string $key): bool
    {
        return in_array($key, [
            'created_time',
            'updated_time',
            'start_time',
            'end_time',
            'backdated_time',
            'issued_at',
            'expires_at',
            'publish_time',
            'joined'
        ], true);
    }

    /**
     * Detects an ISO 8601 formatted string.
     *
     * @see https://developers.facebook.com/docs/graph-api/using-graph-api/#readmodifiers
     * @see http://www.cl.cam.ac.uk/~mgk25/iso-time.html
     * @see http://en.wikipedia.org/wiki/ISO_8601
     */
    public function isIso8601DateString(string $string): bool
    {
        // This insane regex was yoinked from here:
        // http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
        // ...and I'm all like:
        // http://thecodinglove.com/post/95378251969/when-code-works-and-i-dont-know-why
        $crazyInsaneRegexThatSomehowDetectsIso8601 = '/^([+-]?\d{4}(?!\d{2}\b))'
            . '((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?'
            . '|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d'
            . '|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])'
            . '((:?)[0-5]\d)?|24:?00)([.,]\d+(?!:))?)?(\17[0-5]\d'
            . '([.,]\d+)?)?([zZ]|([+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

        return preg_match($crazyInsaneRegexThatSomehowDetectsIso8601, $string) === 1;
    }

    /**
     * Casts a date value from Graph to DateTime.
     *
     * @throws Exception
     */
    public function castToDateTime(int|string $value): DateTime
    {
        if (is_int($value)) {
            $dt = new DateTime();
            $dt->setTimestamp($value);
        } else {
            $dt = new DateTime($value);
        }

        return $dt;
    }

    /**
     * Casts a birthday value from Graph to Birthday
     *
     * @throws Exception
     */
    public function castToBirthday(string $value): Birthday
    {
        return new Birthday($value);
    }

    /**
     * Getter for $graphObjectMap.
     */
    public static function getObjectMap(): array
    {
        return static::$graphObjectMap;
    }

    /**
     * Get the collection of items as JSON.
     *
     * @throws JsonException
     */
    public function asJson(int $options = 0): string
    {
        return json_encode($this->uncastItems(), JSON_THROW_ON_ERROR | $options);
    }

    /**
     * Uncasts any auto-casted datatypes.
     * Basically the reverse of castItems().
     *
     * @return array
     */
    public function uncastItems(): array
    {
        $items = $this->asArray();

        return array_map(static function ($v) {
            if ($v instanceof DateTime) {
                return $v->format(DateTimeInterface::ATOM);
            }

            return $v;
        }, $items);
    }
}
