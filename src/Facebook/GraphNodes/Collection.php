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

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonException;

/**
 * Class Collection
 *
 * Modified version of Collection in "illuminate/support" by Taylor Otwell
 *
 * @package Facebook
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The items contained in the collection.
     */
    protected array $items = [];

    /**
     * Create a new collection.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Gets the value of a field from the Graph node.
     *
     * @param string $name The field to retrieve.
     * @param mixed|null $default The default to return if the field doesn't exist.
     *
     * @return mixed
     */
    public function getField(string $name, mixed $default = null): mixed
    {
        return $this->items[$name] ?? $default;
    }

    /**
     * Returns a list of all fields set on the object.
     */
    public function getFieldNames(): array
    {
        return array_keys($this->items);
    }

    /**
     * Get all the items in the collection.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Run a map over each of the items.
     */
    public function map(Closure $callback): self
    {
        return new self(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Get an item at a given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * Set the item at a given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @throws JsonException
     */
    public function __toString(): string
    {
        return $this->asJson();
    }

    /**
     * Get the collection of items as JSON.
     *
     * @throws JsonException
     */
    public function asJson(int $options = 0): string
    {
        return json_encode($this->asArray(), JSON_THROW_ON_ERROR | $options);
    }

    /**
     * Get the collection of items as a plain array.
     */
    public function asArray(): array
    {
        return array_map(static function ($value) {
            return $value instanceof Collection ? $value->asArray() : $value;
        }, $this->items);
    }
}
