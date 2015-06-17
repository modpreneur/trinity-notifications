<?php

namespace Trinity\NotificationBundle\Tests;


use Doctrine\ORM\EntityManager;
use Necktie\ProductBundle\Entity\BillingPlan;
use Necktie\ProductBundle\Entity\Product;
use Nette\Utils\Strings;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;




class DBTest extends KernelTestCase {


    /** @var  EntityManager */
    protected $em;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }



    public function testUserClients(){
        $users  = $this->em->getRepository("NecktieAppBundle:User");
        $user   = $users->find(23);
        $this->assertEquals(
            ["http://localhost:50020/notify"],
            $user->getNotificationULR()
        );
    }



    public function testDelete(){

        $users  = $this->em->getRepository("NecktieAppBundle:User");
        $user   = $users->find(23);

    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }


}