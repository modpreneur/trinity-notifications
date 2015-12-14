<?php


namespace Trinity\NotificationBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

use Trinity\NotificationBundle\Tests\Sandbox\Entity\Client;
use Trinity\NotificationBundle\Tests\Sandbox\Entity\Product;


class NotificationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('trinity:notification:send')
            ->setDescription('Necktie client command.')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                null
            )
            ->addOption(
                'method',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                'PUT'
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $id = $input->getOption('id');

        //create database
        $command = $this
            ->getApplication()
            ->find("doctrine:schema:update");

        $arguments = [
            "command" => "doctrine:schema:update",
            "--force" => true,
            '-q'
        ];

        $bufferOutput = new BufferedOutput();
        $command->run(new ArrayInput($arguments), $bufferOutput);

        $p = new Product();
        $c = new Client();

        if($id){
            $p->setId($id);
        }

        $p->setClient($c);
        $api = $this->getContainer()->get('trinity.notification.driver.api');


        $table = new Table($output);
        $resutl = $api->execute($p, $c);
        if(is_array($resutl)) $resutl = json_encode($resutl);

        $table
            ->setHeaders(['Server', 'Responce']);
        $table
            ->setRows([
                [
                    'client', $resutl
                ]
            ]);

        $table->render();

    }
}