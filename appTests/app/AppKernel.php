<?php
namespace Trinity\NotificationBundle;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;



/**
 * Class AppKernel.
 */
class AppKernel extends Kernel
{

    /** @var  integer */
    private $port;


    /**
     * Constructor.
     *
     * @param string $environment The environment
     * @param bool $debug Whether to enable debugging or not
     * @param integer $port
     */
    public function __construct($environment, $debug, $port = null)
    {
        $this->port = $port;
        parent::__construct($environment, $debug);
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
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
        ];
    }


    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config.yml');
        $loader->load(__DIR__.'/../../Resources/config/services.yml');

        if ($this->port == 8001) {
            $loader->load(__DIR__.'/client/config.yml');
        } else {
            $loader->load(__DIR__.'/server/config.yml');
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
