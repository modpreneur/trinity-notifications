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
 * @package Trinity\NotificationBundle\AppTests\Controller
 */
class ClientController extends Controller
{

    public function indexAction()
    {
        return new Response('It works!');
    }


    public function productAction(Request $request)
    {
        try {
            $this
                ->get("trinity.notification.services.notification_parser")
                ->parseNotification(
                    json_decode($request->getContent(), true),
                    "Trinity\\NotificationBundle\\Tests\\Sandbox\\Entity\\ClientProduct",
                    $request->getMethod(),
                    $this->getParameter("trinity.notification.server_client_secret")
                );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'code' => 500,
                    'statusCode' => 500,
                    'message' => $e->getMessage(),
                ], 500
            );
        }

        return new JsonResponse(
            [
                'code' => 200,
                'statusCode' => 200,
                'message' => 'OK',
            ]
        );
    }
}
