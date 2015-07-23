<?php
namespace Trinity\NotificationBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Trinity\AnnotationsBundle\Annotations\Notification as Notification;



/**
 * Class TestEntity
 * @package Trinity\NotificationBundle\Tests\Entity
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, desc, date, fullPrice")
 * @Notification\Methods(types={"put", "post", "delete"})
 *
 * @Notification\Url(postfix="no-name-e-entity")
 * @Notification\Url(methods={"put"}, postfix="put-e-entity")
 * @Notification\Url(methods={"delete"}, postfix="delete-e-entity")
 * @Notification\Url(methods={"post"}, postfix="post-e-entity")
 *
 *
 */
class EEntity
{


    private $id = 1;

    private $name = "EE Entity";


    /**
     * @SerializedName("description")
     */
    private $desc = "Description for entity.";


    private $date;



    /**
     * EEntity constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime("2010-11-12");
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


    public function getFullPrice(){
        return "10$";
    }

}