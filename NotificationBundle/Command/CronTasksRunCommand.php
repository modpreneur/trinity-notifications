<?php

    namespace Trinity\NotificationBundle\Command;


    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\StringInput;
    use Symfony\Component\Console\Output\OutputInterface;



    class CronTasksRunCommand extends ContainerAwareCommand
    {
        /** @var  OutputInterface */
        private $output;




        protected function configure()
        {
            $this
                ->setName('notification:cron:run')->setDescription('DisableNotification Cron services.');

        }



        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $output->writeln('<comment>DisableNotification Cron</comment>');

            $this->output = $output;
            $em           = $this->getContainer()->get('doctrine.orm.entity_manager');
            $cronTasks    = $em->getRepository('NecktieNotificationBundle:CronTask')->findAll();

            foreach ($cronTasks as $cronTask) {
                $lastRun = $cronTask->getCreated() ? $cronTask->getCreated()->format('U') : 0;
                $nextRun = $lastRun + $cronTask->getDelay();

                $run = (time() >= $nextRun && $cronTask->getExecute() == NULL);

                if ($run) {
                    $cronTask->setExecute(new \DateTime());
                    $output->writeln(sprintf('Running Cron Task <info>%s</info>', $cronTask->getName()));

                    try {
                        $command = $cronTask->getCommand();
                        $output->writeln(sprintf('Executing command <comment>%s</comment>...', $command['command']));
                        $this->runCommand($command);

                        $output->writeln('<info>CRON:SUCCESS</info>');
                    } catch (\Exception $e) {
                        $output->writeln(sprintf('<error>CRON:ERROR: </error> %s', $e->getMessage()));
                    }

                    $em->persist($cronTask);
                } else {
                    if($cronTask->getExecute() == NULL){
                        $date = new \DateTime();
                        $date->setTimestamp($nextRun);
                        $output->writeln(
                            sprintf(
                                'Skipping Cron Task <info>%s</info>, will be running at %s.',
                                $cronTask->getName(),
                                $date->format('Y-m-d\TH:i:sP')
                            )
                        );
                    }
                }
            }

            $em->flush();
            $output->writeln('<comment>Done!</comment>');
        }



        private function runCommand($arguments)
        {
            $command = $this->getApplication()->find($arguments['command']);
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $this->output);
            return $returnCode;
        }


}