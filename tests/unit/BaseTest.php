<?php
namespace Trinity\NotificationBundle\tests;

/**
 * {@inheritdoc}
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Assert that two DateTime's are equal withing given delta(difference tolleration).
     *
     * @param \DateTime $expected
     * @param \DateTime $actual
     * @param string    $message
     * @param int       $delta
     */
    public function assertDatesTimeEquals(\DateTime $expected, \DateTime $actual, $message = '', $delta = 1)
    {
        $this->assertEquals($expected->getTimestamp(), $actual->getTimestamp(), $message, $delta);
    }
}
