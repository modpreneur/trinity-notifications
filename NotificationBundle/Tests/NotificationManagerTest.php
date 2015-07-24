<?php

namespace Trinity\NotificationBundle\Tests;

use Closure;
use Doctrine\Common\Collections\Collection;
use Traversable;
use Trinity\NotificationBundle\Tests\Entity\Client;
use Trinity\NotificationBundle\Tests\Entity\EntityDisableClient;
use Trinity\NotificationBundle\Tests\Entity\EntityWithoutClient;
use Trinity\NotificationBundle\Tests\Entity\Product;



/**
 * Class NotificationManagerTest
 * @package Trinity\NotificationBundle\Tests
 */
class NotificationManagerTest extends BaseTest
{
    /**
     * @test
     */
    public function testClientToArray()
    {
        $manager = $this->container->get("trinity.notification.client_sender");
        $method = $this->getMethod($manager, "clientsToArray");

        $p = new Product();
        $clients = $method->invokeArgs(
            $manager,
            [$p->getClients()]
        );

        $this->assertNotEmpty($clients);

        foreach ($clients as $client) {
            $this->assertTrue($client instanceof Client);
        }

        // NULL
        $this->assertEquals([], $method->invokeArgs($manager, [null]));

        // Client
        $client = new Client();
        $this->assertEquals([$client], $method->invokeArgs($manager, [$client]));

        // Collection
        $this->assertEquals([$client], $method->invokeArgs($manager, [new TestCollection()]));

        // Array
        $this->assertEquals([$client], $method->invokeArgs($manager, [[$client]]));
    }



    /**
     * @expectedException \Trinity\NotificationBundle\Exception\MethodException
     */
    public function testPrepareURLsError()
    {

        $manager = $this->container->get("trinity.notification.client_sender");
        $method = $this->getMethod($manager, "prepareURL");

        $method->invokeArgs($manager, ["http://example.com", new \stdClass(), "POST"]);
    }



    /**
     * @expectedException \Trinity\NotificationBundle\Exception\ClientException
     */
    public function testPrepareURLsClientError()
    {

        $manager = $this->container->get("trinity.notification.client_sender");
        $method = $this->getMethod($manager, "prepareURL");

        $method->invokeArgs($manager, [null, new Product(), "POST"]);
    }



    /**
     * @test
     */
    public function testPrepareURLs()
    {

        $manager = $this->container->get("trinity.notification.client_sender");
        $method = $this->getMethod($manager, "prepareURL");

        $expected = "http://example.com/product";
        $result = ($method->invokeArgs($manager, ["http://example.com", new Product(), "POST"]));
        $this->assertEquals($expected, $result);

        $expected = "http://example.com/product";
        $result = ($method->invokeArgs($manager, ["http://example.com/", new Product(), "POST"]));
        $this->assertEquals($expected, $result);
    }



    /**
     * @test
     */
    public function testJsonEncodeObject()
    {
        $manager = $this->container->get("trinity.notification.client_sender");
        $method = $this->getMethod($manager, "JSONEncodeObject");

        $expected = "{\"id\":1,\"name\":\"Someone's name\",\"description\":\"Lorem impsu\"";
        $result = $method->invokeArgs($manager, [new Product(), "KJHGHJKKJHJKJHJH"]);

        $this->assertStringStartsWith($expected, $result);

        $this->assertContains("\"hash\":", $result);
        $this->assertContains("\"timestamp\":", $result);
    }



    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     *
     * @expectedExceptionMessage Client error response [url] http://example.com/product [status code] 404 [reason phrase] Not Found
     */
    public function testCreateJSONRequestError()
    {
        $manager = $this->container->get("trinity.notification.client_sender");
        $method = $this->getMethod($manager, "JSONEncodeObject");

        $data = $method->invokeArgs($manager, [new Product(), "KJHGHJKKJHJKJHJH"]);

        $method = $this->getMethod($manager, "createRequest");
        $result = $method->invokeArgs($manager, [$data, "http://example.com/product", "POST", true]);
    }



    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     *
     * @expectedExceptionMessage Client error response [url] http://example.com/product [status code] 404 [reason phrase] Not Found
     */
    public function testCreateJSONRequestError_()
    {
        $manager = $this->container->get("trinity.notification.client_sender");
        $method = $this->getMethod($manager, "JSONEncodeObject");

        $data = $method->invokeArgs($manager, [new Product(), "KJHGHJKKJHJKJHJH"]);

        $method = $this->getMethod($manager, "createRequest");
        $result = $method->invokeArgs($manager, [$data, "http://example.com/product", "POST", false]);
    }



    /**
     * @test
     */
    public function testSendWithoutClient()
    {
        $manager = $this->container->get("trinity.notification.client_sender");

        $entity = new EntityWithoutClient();

        $result = $manager->send($entity);
        $this->assertEmpty($result);
    }



    /**
     * @test
     */
    public function testSendWithDisableClient()
    {
        $manager = $this->container->get("trinity.notification.client_sender");
        $entity = new EntityDisableClient();
        $result = $manager->send($entity);

        $this->assertEmpty($result);
    }

}


// Test collection
class TestCollection implements Collection
{

    /**
     * Adds an element at the end of the collection.
     *
     * @param mixed $element The element to add.
     *
     * @return boolean Always TRUE.
     */
    public function add($element)
    {
        // TODO: Implement add() method.
    }



    /**
     * Clears the collection, removing all elements.
     *
     * @return void
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }



    /**
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element The element to search for.
     *
     * @return boolean TRUE if the collection contains the element, FALSE otherwise.
     */
    public function contains($element)
    {
        // TODO: Implement contains() method.
    }



    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return boolean TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty()
    {
        // TODO: Implement isEmpty() method.
    }



    /**
     * Removes the element at the specified index from the collection.
     *
     * @param string|integer $key The kex/index of the element to remove.
     *
     * @return mixed The removed element or NULL, if the collection did not contain the element.
     */
    public function remove($key)
    {
        // TODO: Implement remove() method.
    }



    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param mixed $element The element to remove.
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeElement($element)
    {
        // TODO: Implement removeElement() method.
    }



    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string|integer $key The key/index to check for.
     *
     * @return boolean TRUE if the collection contains an element with the specified key/index,
     *                 FALSE otherwise.
     */
    public function containsKey($key)
    {
        // TODO: Implement containsKey() method.
    }



    /**
     * Gets the element at the specified key/index.
     *
     * @param string|integer $key The key/index of the element to retrieve.
     *
     * @return mixed
     */
    public function get($key)
    {
        // TODO: Implement get() method.
    }



    /**
     * Gets all keys/indices of the collection.
     *
     * @return array The keys/indices of the collection, in the order of the corresponding
     *               elements in the collection.
     */
    public function getKeys()
    {
        // TODO: Implement getKeys() method.
    }



    /**
     * Gets all values of the collection.
     *
     * @return array The values of all elements in the collection, in the order they
     *               appear in the collection.
     */
    public function getValues()
    {
        // TODO: Implement getValues() method.
    }



    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param string|integer $key The key/index of the element to set.
     * @param mixed $value The element to set.
     *
     * @return void
     */
    public function set($key, $value)
    {
        // TODO: Implement set() method.
    }



    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray()
    {
        return [new Client()];
    }



    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     */
    public function first()
    {
        // TODO: Implement first() method.
    }



    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function last()
    {
        // TODO: Implement last() method.
    }



    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string
     */
    public function key()
    {
        // TODO: Implement key() method.
    }



    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     */
    public function current()
    {
        // TODO: Implement current() method.
    }



    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     */
    public function next()
    {
        // TODO: Implement next() method.
    }



    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param Closure $p The predicate.
     *
     * @return boolean TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
     */
    public function exists(Closure $p)
    {
        // TODO: Implement exists() method.
    }



    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param Closure $p The predicate used for filtering.
     *
     * @return Collection A collection with the results of the filter operation.
     */
    public function filter(Closure $p)
    {
        // TODO: Implement filter() method.
    }



    /**
     * Tests whether the given predicate p holds for all elements of this collection.
     *
     * @param Closure $p The predicate.
     *
     * @return boolean TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
     */
    public function forAll(Closure $p)
    {
        // TODO: Implement forAll() method.
    }



    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param Closure $func
     *
     * @return Collection
     */
    public function map(Closure $func)
    {
        // TODO: Implement map() method.
    }



    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param Closure $p The predicate on which to partition.
     *
     * @return array An array with two elements. The first element contains the collection
     *               of elements where the predicate returned TRUE, the second element
     *               contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(Closure $p)
    {
        // TODO: Implement partition() method.
    }



    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element The element to search for.
     *
     * @return int|string|bool The key/index of the element or FALSE if the element was not found.
     */
    public function indexOf($element)
    {
        // TODO: Implement indexOf() method.
    }



    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     *
     * @return array
     */
    public function slice($offset, $length = null)
    {
        // TODO: Implement slice() method.
    }



    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }



    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }



    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }



    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }



    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }



    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        // TODO: Implement count() method.
    }
}