<?php


namespace Trinity\NotificationBundle\Tests\Sandbox\Entity;


use Doctrine\ORM\Mapping as ORM;
use Trinity\FrameworkBundle\Entity\BaseProduct;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Annotations as Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;


/**
 *
 * @ORM\Entity()
 *
 * @Notification\Source(columns="id, name, description")
 * @Notification\DependentSources(columns="id")
 * @Notification\Methods(types={"put", "post", "delete"})
 */
class Product extends BaseProduct implements NotificationEntityInterface
{
    /** @var  Client */
    private $client;


    private $status = [];


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * Product constructor.
     */
    public function __construct()
    {
        $faker = \Faker\Factory::create();
        $this->id = rand(123, 98765432);

        $this->name = $faker->name;
        $this->description = $faker->text();
    }


    /** @return Client[] */
    public function getClients()
    {
        $c = new Client();

        return [$c];
    }


    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }


    /**
     * @param ClientInterface $client
     * @param string $status
     * @return void
     */
    public function setSyncStatus(ClientInterface $client, $status)
    {
        $this->status[$client->getName()] = $status;
    }
}