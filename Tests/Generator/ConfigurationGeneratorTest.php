<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use marmelab\NgAdminGeneratorBundle\Generator\ConfigurationGenerator;
use marmelab\NgAdminGeneratorBundle\Test\Twig\Loader\TwigTestLoader;

class ConfigurationGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $metadataFactory;

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
        if (!$this->metadataFactory) {
            $driver = new XmlDriver(__DIR__.'/metadata', '.orm.xml');

            $connection = new Connection([], new Driver());

            $configuration = new Configuration();
            $configuration->setMetadataDriverImpl($driver);
            $configuration->setProxyDir(sys_get_temp_dir());
            $configuration->setProxyNamespace('Foo\Proxy');

            $em = EntityManager::create($connection, $configuration);

            $this->metadataFactory = new DisconnectedClassMetadataFactory();
            $this->metadataFactory->setEntityManager($em);
        }

        return $this->metadataFactory->getMetadataFor($className);
    }
}
