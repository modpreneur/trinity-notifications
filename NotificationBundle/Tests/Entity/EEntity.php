<?php
    namespace Trinity\NotificationBundle\Tests\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Trinity\AnnotationsBundle\Annotations\Notification as Notification;


    /**
     * Class TestEntity
     * @package Trinity\NotificationBundle\Tests\Entity
     *
     * @ORM\Entity()
     *
     * @Notification\Source(columns="id, name, description")
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

    }