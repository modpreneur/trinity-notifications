<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.04.16
 * Time: 12:08
 */

namespace Trinity\NotificationBundle\Interfaces;

/**
 * Interface NotificationTypeInterface
 */
interface NotificationTypeInterface
{

    /**
     * Will be called after the
     *
     * @return mixed
     */
    public function onSuccess();


    /**
     * @return mixed
     */
    public function onFailure();
}