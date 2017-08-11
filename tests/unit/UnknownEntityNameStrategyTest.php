<?php
namespace Trinity\NotificationBundle\tests;

use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Notification\UnknownEntityNameStrategy;

/**
 * Class UnknownEntityNameStrategyTest
 */
class UnknownEntityNameStrategyTest extends BaseTest
{
    /**

     *
     * @expectedException Trinity\NotificationBundle\Exception\NotificationException
     * @expectedExceptionMessage
     * No classname found for entityName: 'testing name'.
     * Have you defined it in the configuration under trinity_notification:entities?
     */
    public function testUnknownEntityName(): void
    {
        $notification = new Notification();
        $notification->setEntityName('testing name');

        $strategy = new UnknownEntityNameStrategy();
        $strategy->unknownEntityName($notification);
    }
}
