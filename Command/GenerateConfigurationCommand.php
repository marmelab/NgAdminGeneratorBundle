<?php

namespace marmelab\NgAdminGeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateConfigurationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ng-admin:configuration:generate')
            ->setDescription('Generate a ng-admin valid configuration based on configured REST API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $restRegistry = $container->get('lemon_rest.object_registry');
        $configurationGenerator = $container->get('marmelab.ng_admin_generator.configuration_generator');

        $output->writeln($configurationGenerator->generateConfiguration($restRegistry->all()));
    }
}
