<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Generator;

use Doctrine\ORM\Mapping\ClassMetadata;
use marmelab\NgAdminGeneratorBundle\Generator\ConfigurationGenerator;
use marmelab\NgAdminGeneratorBundle\Test\Twig\Loader\TwigTestLoader;

class ConfigurationGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateConfiguration()
    {
        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->exactly(2))
            ->method('getClassMetadata')
            ->will($this->returnCallback(function($className) {
                return $this->getMetadata($className);
            }));

        $twig = new \Twig_Environment(new TwigTestLoader());

        $generator = new ConfigurationGenerator($emMock, $twig);
        $this->assertEquals(file_get_contents(__DIR__.'/expected/config.js'), $generator->generateConfiguration([
           'Acme\FooBundle\Entity\Post',
           'Acme\FooBundle\Entity\Comment',
        ]));
    }

    private function getMetadata($className)
    {
        $metadata = new ClassMetadata($className);

        switch ($className) {
            case 'Acme\FooBundle\Entity\Post':
                $metadata->fieldMappings = [
                    'id' => [
                        'fieldName' => 'id',
                        'type' => 'integer',
                    ],
                    'title' => [
                        'fieldName' => 'title',
                        'type' => 'string',
                    ],
                    'body' => [
                        'fieldName' => 'body',
                        'type' => 'text'
                    ]
                ];
                break;

            case 'Acme\FooBundle\Entity\Comment':
                $metadata->fieldMappings = [
                    'id' => [
                        'fieldName' => 'id',
                        'type' => 'integer',
                    ],
                    'body' => [
                        'fieldName' => 'body',
                        'type' => 'text',
                    ],
                    'created_at' => [
                        'fieldName' => 'created_at',
                        'type' => 'date'
                    ],
                    'post_id' => [
                        'fieldName' => 'post_id',
                        'type' => 'integer',
                    ],
                ];
                break;
        }

        return $metadata;
    }
}
