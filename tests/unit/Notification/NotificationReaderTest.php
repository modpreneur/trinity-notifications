<?php


use Trinity\NotificationBundle\Entity\NotificationStatus;
use Trinity\NotificationBundle\Notification\NotificationParser;
use Trinity\NotificationBundle\Notification\NotificationReader;
use Trinity\NotificationBundle\Notification\NotificationEventDispatcher;
use Trinity\Bundle\MessagesBundle\Sender\MessageSender;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Notification\EntityAssociator;
use Trinity\NotificationBundle\Interfaces\NotificationLoggerInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

class NotificationReaderTest extends BaseTest
{
    /** @var  PHPUnit_Framework_MockObject_MockObject */
    protected $parserStub;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    protected $eventDispatcherStub;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    protected $messageSenderStub;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    protected $associatorStub;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    protected $loggerStub;

    /** @var  NotificationBatch[] */
    protected $notifications;

    /** @var  PHPUnit_Framework_MockObject_MockObject[] */
    protected $entities;

    /** @var  NotificationBatch */
    protected $batch;

    protected function setUp()
    {
        $this->parserStub = $this->getMockBuilder(NotificationParser::class)->disableOriginalConstructor()->getMock();
        $this->eventDispatcherStub = $this->getMockBuilder(NotificationEventDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->messageSenderStub = $this->getMockBuilder(MessageSender::class)->disableOriginalConstructor()->getMock();
        $this->associatorStub = $this->getMockBuilder(EntityAssociator::class)->disableOriginalConstructor()->getMock();
        $this->loggerStub = $this->getMockBuilder(NotificationLoggerInterface::class)->disableOriginalConstructor()->getMock();

        $this->notifications = [
            (new \Trinity\NotificationBundle\Entity\Notification())->setUid(1),
            (new \Trinity\NotificationBundle\Entity\Notification())->setUid(2),
            (new \Trinity\NotificationBundle\Entity\Notification())->setUid(3)
        ];

        $this->batch = new NotificationBatch();
        $this->batch->addNotifications($this->notifications);

        //create mocks of entities
        $this->entities = [];
        $i = 0;
        foreach ($this->notifications as $notification) {
            $entityMock = $this->getMockBuilder(NotificationEntityInterface::class)->getMock();
            $entityMock->expects(self::any())->method('getId')->willReturn($i++);
            $this->entities[] = $entityMock;
        }
    }

    // tests method 'read'
    public function testReadMessageIsSuccessful()
    {
        //should call dispatchBeforeNotificationBatchProcessEvent once with NotificationBatch
        $this->eventDispatcherStub->expects(self::once())
            ->method('dispatchBeforeNotificationBatchProcessEvent')
            ->with(self::equalTo($this->batch));

        //should call parseNotifications once
        $this->parserStub->expects(self::once())
            ->method('parseNotifications')
            ->with(self::equalTo($this->notifications))
            ->willReturn($this->entities);


        //should call associate once with entities
        $this->associatorStub->expects(self::once())
            ->method('associate')
            ->with(self::equalTo($this->entities));

        ///create SUT mock
        $reader = $this->getMockBuilder(NotificationReader::class)
            ->setConstructorArgs([$this->parserStub, $this->eventDispatcherStub, $this->messageSenderStub, $this->associatorStub])
            ->setMethods(['logNotifications', 'successfullyRead'])->getMock();

        //should call logNotifications once with notification
        $reader->expects(self::once())->method('logNotifications')->with($this->notifications);

        //should call successfullyRead once with batch, notification
        $reader->expects(self::once())->method('successfullyRead')->with($this->batch, $this->entities);

        //dependency injection
        $reader->setNotificationLogger($this->loggerStub);

        $this->assertEquals($this->entities, $reader->read($this->batch));
    }

    // tests method 'read'
    public function testMessageRaisesAssociationEntityNotFoundException()
    {
        //should call dispatchBeforeNotificationBatchProcessEvent once with NotificationBatch
        $this->eventDispatcherStub->expects(self::once())
            ->method('dispatchBeforeNotificationBatchProcessEvent')
            ->with(self::equalTo($this->batch));

        //should call parseNotifications once
        $this->parserStub->expects(self::once())
            ->method('parseNotifications')
            ->with(self::equalTo($this->notifications))
            ->willReturn($this->entities);

        //should call associate once with entities
        $this->associatorStub->expects(self::once())
            ->method('associate')
            ->with(self::equalTo($this->entities))
            ->willThrowException(new AssociationEntityNotFoundException());

        ///create SUT mock
        $reader = $this->getMockBuilder(NotificationReader::class)
            ->setConstructorArgs([$this->parserStub, $this->eventDispatcherStub, $this->messageSenderStub, $this->associatorStub])
            ->setMethods(['logNotifications', 'successfullyRead', 'handleAssociationEntityNotFoundException'])->getMock();

        //should call logNotifications once with notification
        $reader->expects(self::once())->method('logNotifications')->with($this->notifications);

        //should call successfullyRead once with batch, notification
        $reader->expects(self::never())->method('successfullyRead');

        //should call handleAssociationEntityNotFoundException with exception and notificationBatch
        $reader->expects(self::once())->method('handleAssociationEntityNotFoundException')
            ->with(self::isInstanceOf(AssociationEntityNotFoundException::class), $this->batch);

        //dependency injection
        $reader->setNotificationLogger($this->loggerStub);

        //should return all converted entities
        $this->assertEquals($this->entities, $reader->read($this->batch));
    }

    // tests method 'read'
    public function testMessageRaisesGenericException()
    {
        //should call dispatchBeforeNotificationBatchProcessEvent once with NotificationBatch
        $this->eventDispatcherStub->expects(self::once())
            ->method('dispatchBeforeNotificationBatchProcessEvent')
            ->with(self::equalTo($this->batch));

        //should call parseNotifications once
        $this->parserStub->expects(self::once())
            ->method('parseNotifications')
            ->with(self::equalTo($this->notifications))
            ->willReturn($this->entities);

        //should call associate once with entities
        $this->associatorStub->expects(self::once())
            ->method('associate')
            ->with(self::equalTo($this->entities))
            ->willThrowException(new \Exception());

        ///create SUT mock
        $reader = $this->getMockBuilder(NotificationReader::class)
            ->setConstructorArgs([$this->parserStub, $this->eventDispatcherStub, $this->messageSenderStub, $this->associatorStub])
            ->setMethods(['logNotifications', 'successfullyRead', 'handleGenericException'])->getMock();

        //should call logNotifications once with notification
        $reader->expects(self::once())->method('logNotifications')->with($this->notifications);

        //should call successfullyRead once with batch, notification
        $reader->expects(self::never())->method('successfullyRead');

        //should call handleGenericException with exception and notificationBatch
        $reader->expects(self::once())->method('handleGenericException')
            ->with(self::isInstanceOf(\Exception::class), $this->batch);

        //dependency injection
        $reader->setNotificationLogger($this->loggerStub);

        //should return all converted entities
        $this->assertEquals($this->entities, $reader->read($this->batch));
    }

    // tests method 'testSuccessfullyRead'
    public function testSuccessfullyRead()
    {
        //should call dispatchChangesDoneEvent once with entities, batch
        $this->eventDispatcherStub->expects(self::once())
            ->method('dispatchChangesDoneEvent')
            ->with(self::equalTo($this->entities), self::equalTo($this->batch));

        //should call dispatchAfterNotificationBatchProcessEvent once with  batch
        $this->eventDispatcherStub->expects(self::once())
            ->method('dispatchAfterNotificationBatchProcessEvent')
            ->with(self::equalTo($this->batch));

        ///create SUT mock
        $reader = $this->getMockBuilder(NotificationReader::class)
            ->setConstructorArgs([$this->parserStub, $this->eventDispatcherStub, $this->messageSenderStub, $this->associatorStub])
            ->setMethods(['logNotificationsStatus', 'sendStatusMessage'])->getMock();

        //should call logNotificationsSuccess once with batch
        $reader->expects(self::once())->method('logNotificationsStatus')->with($this->batch,NotificationStatus::STATUS_OK , 'ok');

        //should call sendStatusMessage once with batch
        $reader->expects(self::once())->method('sendStatusMessage')->with($this->batch);

        //dependency injection
        $reader->setNotificationLogger($this->loggerStub);

        //should return all converted entities
        $this->invokeMethod($reader, 'successfullyRead', [$this->batch, $this->entities]);
    }
}