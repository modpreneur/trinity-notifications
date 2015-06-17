<?php

namespace Trinity\NotificationBundle\Controller;


use Trinity\NotificationBundle\Entity\CronTask;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


class CronTaskController extends Controller
{
    /**
     * @Route("/admin/cron", name="notification_test")
     * @todo remove
     */
    public function testAction()
    {
        $entity = new CronTask();

        $entity
            ->setName('Example asset symlinking task')
            ->setDelay(3600) // Run once every hour
            ->setCreated(new \DateTime())
            ->setCommand(
                'assets:install --symlink web'
            );

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        return new Response('OK!');
    }


}