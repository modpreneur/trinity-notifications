<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 12.12.15
 * Time: 15:51
 */

namespace Trinity\NotificationBundle\AppTests\Sandbox\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trinity\FrameworkBundle\Entity\BaseProduct;
use Trinity\NotificationBundle\Annotations as Notification;


/**
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, description")
 * @Notification\DependentSources(columns="id")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class ClientProduct extends BaseProduct
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $serverId;


    /**
     * @return mixed
     */
    public function getServerId()
    {
        return $this->serverId;
    }


    /**
     * @param mixed $serverId
     * @return $this
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;

        return $this;
    }


}