<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 27.05.16
 * Time: 8:43
 */

namespace Trinity\NotificationBundle\Interfaces;

use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Interface NotificationEntityRepositoryInterface
 *
 * This interface MUST be implemented by all repositories of all notification entities!
 *
 * @package Trinity\NotificationBundle\Interfaces
 */
interface NotificationEntityRepositoryInterface
{
    /**
     * Select entity by id. Set fetch mode to "EAGER" to load all data.
     *
     * @param $id
     *
     * @return NotificationEntityInterface
     */
    public function findEagerly($id);
}
