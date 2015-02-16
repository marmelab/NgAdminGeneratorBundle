<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Generator;

use marmelab\NgAdminGeneratorBundle\Generator\ConfigurationGenerator;
use marmelab\NgAdminGeneratorBundle\Test\Twig\Loader\TwigTestLoader;

class ConfigurationGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateConfiguration()
    {
        $retrieverMock = $this->getMockBuilder('marmelab\NgAdminGeneratorBundle\Retriever\EntityConfigurationRetriever')
            ->disableOriginalConstructor()
            ->getMock();

        $retrieverMock->expects($this->at(0))
            ->method('retrieveEntityConfiguration')
            ->will($this->returnValue([
                ['name' => 'id', 'type' => 'integer'],
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'body', 'type' => 'text'],
            ]));

        $retrieverMock->expects($this->at(1))
            ->method('retrieveEntityConfiguration')
            ->will($this->returnValue([
                ['name' => 'id', 'type' => 'integer'],
                ['name' => 'body', 'type' => 'text'],
                ['name' => 'created_at', 'type' => 'date'],
                ['name' => 'post_id', 'type' => 'integer'],
            ]));

        $twig = new \Twig_Environment(new TwigTestLoader());

        $generator = new ConfigurationGenerator($retrieverMock, $twig);
        $this->assertEquals(file_get_contents(__DIR__.'/expected/config.js'), $generator->generateConfiguration([
           'Acme\FooBundle\Entity\Post',
           'Acme\FooBundle\Entity\Comment',
        ]));
    }
}
