<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.05.16
 * Time: 11:15
 */

namespace Trinity\NotificationBundle\RabbitMQ;

use Bunny\Client;
use Trinity\Bundle\BunnyBundle\Setup\BaseRabbitSetup;

/**
 * Class NotificationSetup
 *
 * @package Trinity\NotificationBundle\RabbitMQ
 */
class NotificationSetup extends BaseRabbitSetup
{
    /** @var string */
    protected $outputNotificationsExchange;


    /**
     * BaseRabbitSetup constructor.
     *
     * @param Client   $client
     * @param string[] $listeningQueues
     * @param string   $outputMessagesExchange
     * @param string   $outputNotificationsExchange
     */
    public function __construct(
        Client $client,
        array $listeningQueues,
        string $outputMessagesExchange,
        string $outputNotificationsExchange
    ) {
        parent::__construct($client, $listeningQueues, $outputMessagesExchange);

        $this->outputNotificationsExchange = $outputNotificationsExchange;
    }


    /**
     * @return string
     */
    public function getOutputNotificationsExchange() : string
    {
        return $this->outputNotificationsExchange;
    }


    /**
     * @param string $outputNotificationsExchange
     */
    public function setOutputNotificationsExchange(string $outputNotificationsExchange)
    {
        $this->outputNotificationsExchange = $outputNotificationsExchange;
    }
}
