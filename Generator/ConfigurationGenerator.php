<?php

namespace marmelab\NgAdminGeneratorBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;

class ConfigurationGenerator
{
    private $em;
    private $twig;

    public function __construct(EntityManagerInterface $em, \Twig_Environment $twig)
    {
        $this->em = $em;
        $this->twig = $twig;
    }

    public function generateConfiguration(array $classNames)
    {
        $entities = [];
        foreach ($classNames as $className) {
            $classNameParts = explode('\\', $className);
            $varName = lcfirst(end($classNameParts));

            $entities[$varName] = $this->em->getClassMetadata($className);
        }

        return $this->twig->render('marmelabNgAdminGeneratorBundle:Configuration:config.js.twig', compact('entities'));
    }
}
