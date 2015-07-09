<?php


	use Symfony\Component\HttpKernel\Kernel;
	use Symfony\Component\Config\Loader\LoaderInterface;

	class AppKernel extends Kernel
	{
		public function registerBundles()
		{
			return array(
                new Braincrafted\Bundle\TestingBundle\BraincraftedTestingBundle($this),
				new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new \Trinity\NotificationBundle\TrinityNotificationBundle()
			);
		}

		public function registerContainerConfiguration(LoaderInterface $loader)
		{
			$loader->load(__DIR__.'/config.yml');
		}

        /**
         * @return string
         */
        public function getCacheDir()
        {
            return sys_get_temp_dir().'/./cache';
        }

        /**
         * @return string
         */
        public function getLogDir()
        {
            return sys_get_temp_dir().'/./logs';
        }
	}