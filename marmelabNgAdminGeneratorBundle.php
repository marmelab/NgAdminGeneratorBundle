<?php

namespace marmelab\NgAdminGeneratorBundle;

use marmelab\NgAdminGeneratorBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class marmelabNgAdminGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TransformerPass());
    }
}
