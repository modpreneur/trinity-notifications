<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 10.03.16
 * Time: 14:41
 */

namespace Trinity\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerProduceMessageCommand extends ContainerAwareCommand
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $product = $this->getContainer()->get("doctrine")->getRepository("NecktieAppBundle:Product")->find($input->getArgument("product"));
        $user = $this->getContainer()->get("doctrine")->getRepository("NecktieAppBundle:User")->find($input->getArgument("user"));
        $billingPlan = $this->getContainer()->get("doctrine")->getRepository("NecktieAppBundle:BillingPlan")->find($input->getArgument("billing-plan"));

        $driver = $this->getContainer()->get("trinity.notification.driver.rabbit.server");

        $driver->execute($product, null, ["HTTPMethod" => "PUT"]);
        $driver->execute($billingPlan, null, ["HTTPMethod" => "PUT"]);
        $driver->execute($user, null, ["HTTPMethod" => "PUT"]);

        $this->getContainer()->get("trinity.notification.batch_manager")->send();
        $output->writeln("message sent");
    }


    protected function configure()
    {
        $this->setName('trinity:notification:server:message:produce');

        $this->addArgument('product', InputArgument::OPTIONAL, 'Product id?');
        $this->addArgument('user', InputArgument::OPTIONAL, 'User id?');
        $this->addArgument('billing-plan', InputArgument::OPTIONAL, 'BP id?');
    }
}
