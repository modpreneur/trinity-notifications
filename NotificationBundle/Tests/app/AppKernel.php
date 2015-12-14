<?php


use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;


/**
 * Class AppKernel.
 */
class AppKernel extends Kernel
{

    private $port;

    private $client = false;


    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }


    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
        $this->client = $port == 8000;
    }


    /**
     * @return array
     */
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Trinity\NotificationBundle\TrinityNotificationBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),

            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle()
        ];
    }


    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config.yml');
        $loader->load(__DIR__.'/../../Resources/config/services.yml');

        if($this->client){
            $loader->load(__DIR__.'/client/config.yml');
        }else{
            $loader->load(__DIR__.'/master/config.yml');
        }
    }


    /**
     * @return string
     */
    public function getCacheDir()
    {
        return __DIR__.'/./cache';
    }


    /**
     * @return string
     */
    public function getLogDir()
    {
        return __DIR__.'/./logs';
    }
}
