<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 02.06.16
 * Time: 6:22
 */

namespace Trinity\NotificationBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trinity\NotificationBundle\Event\DisableNotificationEvent;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\RabbitMQ\MessageConsumer;

/**
 * Class BaseConsumerCommand
 *
 * @package Trinity\NotificationBundle\Command
 */
abstract class BaseConsumerCommand extends ContainerAwareCommand
{
    /**
     * @return MessageConsumer
     */
    abstract public function getConsumer() : MessageConsumer;


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        if ($dispatcher->hasListeners(Events::DISABLE_NOTIFICATION)) {
            $event = new DisableNotificationEvent();
            $dispatcher->dispatch(
                Events::DISABLE_NOTIFICATION,
                $event
            );
        }

        $this->getConsumer()->startConsuming($input->getArgument('queue'), $input->getArgument('count'));
    }


    /**
     *
     */
    protected function configure()
    {
        $this->addArgument(
            'queue',
            InputArgument::REQUIRED,
            'Queue to listen.'
        );

        $this->addArgument(
            'count',
            InputArgument::REQUIRED,
            'Count of messages to be read. The consumer should exit after reading specified count of messages.'
        );
    }
}