<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Entity;

use Trinity\Component\EntityCore\Entity\ClientInterface;

/**
 * Interface NotificationEntityInterface
 *
 * @package Trinity\NotificationBundle\Entity
 */
interface NotificationEntityInterface
{
    /** @return int */
    public function getId();


    /** @return ClientInterface[] */
    public function getClients();

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageType Type of the message, which is displayed to the user.
     * @param string          $statusMessageUid  Identification of the message.
     * @param \DateTime       $changedAt         DateTime when the status was changed. Default value is new
     *                                           \DateTime('now')
     *
     * @return
     */
    public function setNotificationStatus(
        ClientInterface $client,
        string $statusMessageType,
        string $statusMessageUid,
        \DateTime $changedAt = null
    );

    /**
     * Returns updatedAt value.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationInProgress(
        ClientInterface $client,
        string $statusMessageUid,
        \DateTime $changedAt = null
    );

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationNotSynchronized(
        ClientInterface $client,
        string $statusMessageUid,
        \DateTime $changedAt = null
    );

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationError(
        ClientInterface $client,
        string $statusMessageUid,
        \DateTime $changedAt = null
    );

    /**
     * @param ClientInterface $client
     * @param string          $statusMessageUid
     *
     * @param \DateTime       $changedAt DateTime when the status was changed. Default value is new \DateTime('now')
     */
    public function setNotificationSynchronized(
        ClientInterface $client,
        string $statusMessageUid,
        \DateTime $changedAt = null
    );
}
