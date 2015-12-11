<?php

/**
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Controller\Tests;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ClientController
 *
 * @package Trinity\NotificationBundle\Tests\Controller
 */
class ClientController extends Controller
{

    public function indexAction(){

        return new Response('It\'s work!');
    }


    public function productAction(Request $request){

        $r = json_decode($request->getContent(), true);

        if($r['id'] == 1){
            return new JsonResponse([
                'code'       => 500,
                'statusCode' => 500,
                'message'    => 'Product already exist.'
            ]);
        }

        return new JsonResponse([
            'code'       => 200,
            'statusCode' => 200,
            'message'    => 'OK'
        ]);
    }


}
