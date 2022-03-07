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

use Facebook\GraphNodes\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{

    public function testAnExistingPropertyCanBeAccessed(): void
    {
        $graphNode = new Collection(['foo' => 'bar']);

        $field = $graphNode->getField('foo');
        $this->assertEquals('bar', $field);

        $property = $graphNode->getField('foo');
        $this->assertEquals('bar', $property);
    }

    public function testAMissingPropertyWillReturnNull(): void
    {
        $graphNode = new Collection(['foo' => 'bar']);
        $field = $graphNode->getField('baz');

        $this->assertNull($field, 'Expected the property to return null.');
    }

    public function testAMissingPropertyWillReturnTheDefault(): void
    {
        $graphNode = new Collection(['foo' => 'bar']);

        $field = $graphNode->getField('baz', 'faz');
        $this->assertEquals('faz', $field);

        $property = $graphNode->getField('baz', 'faz');
        $this->assertEquals('faz', $property);
    }

    public function testFalseDefaultsWillReturnSameType(): void
    {
        $graphNode = new Collection(['foo' => 'bar']);

        $field = $graphNode->getField('baz', '');
        $this->assertSame('', $field);

        $field = $graphNode->getField('baz', 0);
        $this->assertSame(0, $field);

        $field = $graphNode->getField('baz', false);
        $this->assertFalse($field);
    }

    public function testTheKeysFromTheCollectionCanBeReturned(): void
    {
        $graphNode = new Collection([
            'key1' => 'foo',
            'key2' => 'bar',
            'key3' => 'baz',
        ]);

        $fieldNames = $graphNode->getFieldNames();
        $this->assertEquals(['key1', 'key2', 'key3'], $fieldNames);

        $propertyNames = $graphNode->getFieldNames();
        $this->assertEquals(['key1', 'key2', 'key3'], $propertyNames);
    }

    public function testAnArrayCanBeInjectedViaTheConstructor(): void
    {
        $collection = new Collection(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $collection->asArray());
    }

    public function testACollectionCanBeConvertedToProperJson(): void
    {
        $collection = new Collection(['foo', 'bar', 123]);

        $collectionAsString = $collection->asJson();

        $this->assertEquals('["foo","bar",123]', $collectionAsString);
    }

    public function testACollectionCanBeCounted(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz']);

        $collectionCount = count($collection);

        $this->assertEquals(3, $collectionCount);
    }

    public function testACollectionCanBeAccessedAsAnArray(): void
    {
        $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

        $this->assertEquals('bar', $collection['foo']);
        $this->assertEquals('baz', $collection['faz']);
    }

    public function testACollectionCanBeIteratedOver(): void
    {
        $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

        $this->assertInstanceOf('IteratorAggregate', $collection);

        $newArray = [];

        foreach ($collection as $k => $v) {
            $newArray[$k] = $v;
        }

        $this->assertEquals(['foo' => 'bar', 'faz' => 'baz'], $newArray);
    }
}
