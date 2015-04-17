<?php

namespace marmelab\NgAdminGeneratorBundle\Generator;

class ConfigurationGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $objectDefinition;
    private $transformers;

    public function setUp()
    {
        $this->objectDefinitions = array();
    }

    public function testGenerateConfigurationWithObjectDefinitionsEmpty()
    {
        $this->setExpectedException('\RuntimeException');

        $em   = $this->getEntityManagerMock();
        $twig = $this->getTwigMock();

        $classNameToNgAdminConfigurationTransformer = $this->getClassNameToNgAdminConfigurationTransformerMock();
        $ngAdminWithRelationshipsTransformer        = $this->getNgAdminWithRelationshipsTransformerMock();
        $entityToEntityWithIconTransformer          = $this->getEntityToEntityWithIconTransformerMock();

        $this->transformers = array(
            $classNameToNgAdminConfigurationTransformer,
            $ngAdminWithRelationshipsTransformer,
            $entityToEntityWithIconTransformer
        );

        $configurationGenerator = new ConfigurationGenerator(
            $this->transformers,
            $this->getEntityManagerMock(),
            $this->getTwigMock()
        );

        $configurationGenerator->generateConfiguration($this->objectDefinitions);
    }

    private function getEntityManagerMock()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $em;
    }

    private function getTwigMock()
    {
        $twig = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        return $twig;
    }

    private function getClassNameToNgAdminConfigurationTransformerMock()
    {
        $transformerClass = 'marmelab\NgAdminGeneratorBundle\Transformer\ClassNameToNgAdminConfigurationTransformer';
        $classNameToNgAdminConfigurationTransformer = $this->getMockBuilder($transformerClass)
            ->disableOriginalConstructor()
            ->getMock();

        return $classNameToNgAdminConfigurationTransformer;
    }

    private function getNgAdminWithRelationshipsTransformerMock()
    {
        $transformerClass = 'marmelab\NgAdminGeneratorBundle\Transformer\NgAdminWithRelationshipsTransformer';
        $ngAdminWithRelationshipsTransformer = $this->getMockBuilder($transformerClass)
            ->disableOriginalConstructor()
            ->getMock();

        return $ngAdminWithRelationshipsTransformer;
    }

    private function getEntityToEntityWithIconTransformerMock()
    {
        $transformerClass = 'marmelab\NgAdminGeneratorBundle\Transformer\EntityToEntityWithIconTransformer';
        $entityToEntityWithIconTransformer = $this->getMockBuilder($transformerClass)
            ->disableOriginalConstructor()
            ->getMock();

        return $entityToEntityWithIconTransformer;
    }
}
