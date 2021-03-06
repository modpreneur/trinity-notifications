<?php

namespace Trinity\NotificationBundle\AppTests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\Component\Core\Interfaces\ClientInterface as CI;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\AppTests\Sandbox\Entity\Client;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, description")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class EntityDisableClient implements NotificationEntityInterface
{
    /** @var  int */
    private $id = 1;

    /** @var  string */
    private $name;

    /** @var  string */
    private $description;

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Disable TestClient';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Disable client description.';
    }

    /** @return CI[] */
    public function getClients()
    {
        $c = new Client();

        return [$c];
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
