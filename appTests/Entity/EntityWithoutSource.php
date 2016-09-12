<?php

namespace Trinity\NotificationBundle\AppTests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\Component\Core\Interfaces\ClientInterface as CI;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityWithoutSource implements NotificationEntityInterface
{
    /** @return CI[] */
    public function getClients()
    {
        // TODO: Implement getClients() method.
    }

    /**
     * @param CI     $client
     * @param string $status
     */
    public function setSyncStatus(CI $client, $status)
    {
        // TODO: Implement setSyncStatus() method.
    }
}
