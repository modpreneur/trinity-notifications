<?php

/**
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Controller\Tests;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Trinity\NotificationBundle\Annotations\DisableNotification;


/**
 * Class ClientController
 * @DisableNotification()
 *
 * @package Trinity\NotificationBundle\Tests\Controller
 */
class ClientController extends Controller
{

    public function indexAction()
    {

        return new Response('It\'s work!');
    }


    public function productAction(Request $request)
    {
        try {
            $this->get("trinity.notification.services.notification_parser")
                ->parseNotification(
                    $request->request->all(),
                    "Trinity\\NotificationBundle\\Tests\\Sandbox\\Entity\\ClientProduct",
                    $request->getMethod(),
                    $this->getParameter("trinity.notification.master_client_secret")
                );
        }
        catch (\Exception $e) {
            return new JsonResponse([
                'code' => 500,
                'statusCode' => 500,
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'code' => 200,
            'statusCode' => 200,
            'message' => 'OK'
        ]);


    }
}
