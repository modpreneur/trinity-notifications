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
     */
    public function __construct(
        Client $client,
        $serverToClientsDeadLetterExchangeName,
        $serverToClientsDeadLetterQueueName,
        $serverToClientsExchangeName,
        $serverToClientsQueueNamePattern,
        $clientsToServerDeadLetterExchangeName,
        $clientsToServerDeadLetterQueueName,
        $clientsToServerExchangeName,
        $clientsToServerQueueName
    )
    {
        parent::__construct($client);

        $this->serverToClientsDeadLetterExchangeName = $serverToClientsDeadLetterExchangeName;
        $this->serverToClientsDeadLetterQueueName = $serverToClientsDeadLetterQueueName;
        $this->serverToClientsExchangeName = $serverToClientsExchangeName;
        $this->serverToClientsQueueNamePattern = $serverToClientsQueueNamePattern;

        $this->clientsToServerDeadLetterExchangeName = $clientsToServerDeadLetterExchangeName;
        $this->clientsToServerDeadLetterQueueName = $clientsToServerDeadLetterQueueName;
        $this->clientsToServerExchangeName = $clientsToServerExchangeName;
        $this->clientsToServerQueueName = $clientsToServerQueueName;
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
    }

    /**
     * Setup the clients to server part.
     */
    protected function setupClientToServer()
    {
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
     * @inheritdoc
     */
    public function getListeningQueue()
    {
        return $this->clientsToServerQueueName;
    }

    /**
     * @inheritdoc
     */
    public function getOutputRoutingKey(array $data = [])
    {
        return str_replace(self::ID_PATTERN_WILDCARD, $data["clientId"], $this->serverToClientsQueueNamePattern);
    }
}