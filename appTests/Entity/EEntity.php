<?php

namespace Trinity\NotificationBundle\AppTests\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Trinity\FrameworkBundle\Entity\ClientInterface as CI;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\AppTests\Sandbox\Entity\Client;


/**
 * Class TestEntity.
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, desc, date, fullPrice, testMethod")
 * @Notification\Methods(types={"put", "post", "delete"})
 *
 * @Notification\Url(postfix="no-name-e-entity")
 * @Notification\Url(methods={"put"}, postfix="put-e-entity")
 * @Notification\Url(methods={"delete"}, postfix="delete-e-entity")
 * @Notification\Url(methods={"post"}, postfix="post-e-entity")
 */
class EEntityInterface implements NotificationEntityInterface
{
    /**
     * @var int
     */
    private $id = 1;

    /**
     * @var string
     */
    private $name = 'EE Entity';

    /**
     * @SerializedName("description")
     */
    private $desc = 'Description for entity.';

    /**
     * @var \DateTime
     */
    private $date;


    /**
     * EEntityInterface constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime('2010-11-12');
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }


    /**
     * @param mixed $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }


    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }


    /**
     * @return string
     */
    public function getFullPrice()
    {
        return '10$';
    }


    /**
     * @SerializedName("test-method")
     *
     * @return string
     */
    public function testMethod()
    {
        return 'test';
    }


    /** @return CI[] */
    public function getClients()
    {
        return [new Client()];
    }


    /**
     * @param CI $client
     * @param string $status
     * @return void
     */
    public function setSyncStatus(CI $client, $status)
    {
        // TODO: Implement setSyncStatus() method.
    }
}
