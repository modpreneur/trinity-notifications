<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 19.03.16
 * Time: 11:20
 */

namespace Trinity\NotificationBundle\RabbitMQ;


use Bunny\Client;

class ServerSetup extends BaseRabbitSetup
{
    const ID_PATTERN_WILDCARD = ":ID";


    /**
     * @var string
     */
    protected $serverToClientsDeadLetterExchangeName;


    /**
     * @var string
     */
    protected $serverToClientsDeadLetterQueueName;


    /**
     * @var string
     */
    protected $serverToClientsExchangeName;


    /**
     * @var string
     */
    protected $serverToClientsQueueNamePattern;


    /**
     * @var string
     */
    protected $clientsToServerDeadLetterExchangeName;


    /**
     * @var string
     */
    protected $clientsToServerDeadLetterQueueName;


    /**
     * @var string
     */
    protected $clientsToServerExchangeName;


    /**
     * @var string
     */
    protected $clientsToServerQueueName;


    /**
     * @var string
     */
    protected $clientsToServerDeadNotificationsErrorMessagesExchangeName;


    /**
     * @var string
     */
    protected $clientsToServerDeadNotificationsErrorMessagesQueueName;


    /**
     * @var string
     */
    protected $serverToClientsDeadNotificationsErrorMessagesExchangeName;


    /**
     * @var string
     */
    protected $serverToClientsDeadNotificationsErrorMessagesQueueName;



    /**
     * ServerSetup constructor.
     * @param Client $client
     * @param string $serverToClientsDeadLetterExchangeName
     * @param string $serverToClientsDeadLetterQueueName
     * @param string $serverToClientsExchangeName
     * @param string $serverToClientsQueueNamePattern
     * @param string $clientsToServerDeadLetterExchangeName
     * @param string $clientsToServerDeadLetterQueueName
     * @param string $clientsToServerExchangeName
     * @param string $clientsToServerQueueName
     * @param $clientsToServerDeadNotificationsErrorMessagesExchangeName
     * @param $clientsToServerDeadNotificationsErrorMessagesQueueName
     * @param $serverToClientsDeadNotificationsErrorMessagesExchangeName
     * @param $serverToClientsDeadNotificationsErrorMessagesQueueName
     * @param $listeningQueue
     * @param $outputErrorMessagesExchangeName
     */
    public function __construct(
        Client $client,
        $listeningQueue,
        $outputErrorMessagesExchangeName,
        $serverToClientsDeadLetterExchangeName,
        $serverToClientsDeadLetterQueueName,
        $serverToClientsExchangeName,
        $serverToClientsQueueNamePattern,
        $clientsToServerDeadLetterExchangeName,
        $clientsToServerDeadLetterQueueName,
        $clientsToServerExchangeName,
        $clientsToServerQueueName,
        $clientsToServerDeadNotificationsErrorMessagesExchangeName,
        $clientsToServerDeadNotificationsErrorMessagesQueueName,
        $serverToClientsDeadNotificationsErrorMessagesExchangeName,
        $serverToClientsDeadNotificationsErrorMessagesQueueName
    )
    {
        parent::__construct($client, $listeningQueue, $outputErrorMessagesExchangeName);

        $this->serverToClientsDeadLetterExchangeName = $serverToClientsDeadLetterExchangeName;
        $this->serverToClientsDeadLetterQueueName = $serverToClientsDeadLetterQueueName;
        $this->serverToClientsExchangeName = $serverToClientsExchangeName;
        $this->serverToClientsQueueNamePattern = $serverToClientsQueueNamePattern;

        $this->clientsToServerDeadLetterExchangeName = $clientsToServerDeadLetterExchangeName;
        $this->clientsToServerDeadLetterQueueName = $clientsToServerDeadLetterQueueName;
        $this->clientsToServerExchangeName = $clientsToServerExchangeName;
        $this->clientsToServerQueueName = $clientsToServerQueueName;

        $this->clientsToServerDeadNotificationsErrorMessagesExchangeName = $clientsToServerDeadNotificationsErrorMessagesExchangeName;
        $this->clientsToServerDeadNotificationsErrorMessagesQueueName = $clientsToServerDeadNotificationsErrorMessagesQueueName;
        $this->serverToClientsDeadNotificationsErrorMessagesExchangeName = $serverToClientsDeadNotificationsErrorMessagesExchangeName;
        $this->serverToClientsDeadNotificationsErrorMessagesQueueName = $serverToClientsDeadNotificationsErrorMessagesQueueName;

        $this->listeningQueue = $listeningQueue;
        $this->outputErrorMessagesExchangeName = $outputErrorMessagesExchangeName;
    }


    /**
     * Set up the rabbit queue, exchanges and so.
     */
    public function setUp()
    {
        parent::setUp();

        $this->setupServerToClients();

        $this->setupClientToServer();
    }


    /**
     * Setup the server to clients part.
     */
    protected function setupServerToClients()
    {
        //declare output clients dead letter exchange
        $this->channel->exchangeDeclare($this->serverToClientsDeadLetterExchangeName, "direct", false, true);

        //declare output clients dead letter queue
        $this->channel->queueDeclare($this->serverToClientsDeadLetterQueueName, false, true);

        //bind output dead letter queue to dead letter exchange
        $this->channel->queueBind($this->serverToClientsDeadLetterQueueName, $this->serverToClientsDeadLetterExchangeName);

        //declare output clients exchange
        $this->channel->exchangeDeclare($this->serverToClientsExchangeName, "direct", false, true);

        //declare error messages queue
        $this->channel->queueDeclare($this->serverToClientsDeadNotificationsErrorMessagesQueueName, false, true);

        //declare error messages exchange
        $this->channel->exchangeDeclare($this->serverToClientsDeadNotificationsErrorMessagesExchangeName, "direct", false, true);

        //bind output error messages queue to error messages exchange
        $this->channel->queueBind($this->serverToClientsDeadNotificationsErrorMessagesQueueName, $this->serverToClientsDeadNotificationsErrorMessagesExchangeName);

    }

    /**
     * Setup the clients to server part.
     */
    protected function setupClientToServer()
    {

        //dump($this->clientsToServerQueueName);die();
        //declare input clients exchange
        $this->channel->exchangeDeclare($this->clientsToServerExchangeName, "direct", false, true);

        //declare input clients queue
        $this->channel->queueDeclare($this->clientsToServerQueueName, false, true, false, false, false, [
            "x-dead-letter-exchange" => $this->clientsToServerDeadLetterExchangeName,
            "x-dead-letter-routing-key" => "",
        ]);

        //bind input clients queue to exchange
        $this->channel->queueBind($this->clientsToServerQueueName, $this->clientsToServerExchangeName);

        //declare output clients dead letter exchange
        $this->channel->exchangeDeclare($this->clientsToServerDeadLetterExchangeName, "direct", false, true);

        //declare output clients dead letter queue
        $this->channel->queueDeclare($this->clientsToServerDeadLetterQueueName, false, true);

        //bind output dead letter queue to dead letter exchange
        $this->channel->queueBind($this->clientsToServerDeadLetterQueueName, $this->clientsToServerDeadLetterExchangeName);

        //declare error messages queue
        $this->channel->queueDeclare($this->clientsToServerDeadNotificationsErrorMessagesQueueName, false, true);

        //declare error messages exchange
        $this->channel->exchangeDeclare($this->clientsToServerDeadNotificationsErrorMessagesExchangeName, "direct", false, true);

        //bind output error messages queue to error messages exchange
        $this->channel->queueBind($this->clientsToServerDeadNotificationsErrorMessagesQueueName, $this->clientsToServerDeadNotificationsErrorMessagesExchangeName);
    }


    /**
     * Create queue for client id and bind it to the exchange.
     *
     * @param string $clientId
     */
    public function createClientQueue(string $clientId)
    {
        $queueName = $this->getOutputRoutingKey(["clientId" => $clientId]);
        $this->declareServerToClientQueue($queueName);
        $this->bindServerToClientQueueToExchange($queueName);
    }

    /**
     * Declare server to client queue with dead letter exchange.
     *
     * @param string $queueName
     */
    public function declareServerToClientQueue(string $queueName)
    {
        $this->channel->queueDeclare($queueName, false, true, false, false, false, [
            "x-dead-letter-exchange" => $this->serverToClientsDeadLetterExchangeName,
            "x-dead-letter-routing-key" => "",
        ]);
    }

    /**
     * Bind server to client queue to exchange.
     *
     * @param string $queueName
     */
    public function bindServerToClientQueueToExchange(string $queueName)
    {
        $this->channel->queueBind($queueName, $this->serverToClientsExchangeName, $queueName);
    }


    /**
     * @inheritdoc
     */
    public function getOutputExchangeName()
    {
        return $this->serverToClientsExchangeName;
    }


    /**
     * Get routing key which will be used to route messages to queue.
     *
     * @param array $data Additional data required to generate routing key.
     *
     * @return string
     */
    public function getOutputRoutingKey(array $data = [])
    {
        return str_replace(self::ID_PATTERN_WILDCARD, $data["clientId"], $this->serverToClientsQueueNamePattern);
    }
}
