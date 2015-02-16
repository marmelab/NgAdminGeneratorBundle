<?php

namespace marmelab\NgAdminGeneratorBundle\Generator;

class ConfigurationGenerator
{
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generateConfiguration(array $entities)
    {
        foreach ($entities as $className => $fields) {
        }

        return $this->twig->render('marmelabNgAdminGeneratorBundle:Configuration:config.js.twig', compact('entities'));
    }
}
