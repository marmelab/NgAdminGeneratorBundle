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
    public function testGenerateConfiguration()
    {
        $metadataFactory = $this->getMetadataFactory();

        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->exactly(3))
            ->method('getClassMetadata')
            ->will($this->returnCallback(function($className) use($metadataFactory) {
                return $metadataFactory->getMetadataFor($className);
            }));

        $emMock->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $twig = new \Twig_Environment(new TwigTestLoader());

        $generator = new ConfigurationGenerator($emMock, $twig);
        $this->assertEquals(file_get_contents(__DIR__.'/expected/config.js'), $generator->generateConfiguration([
           'Acme\FooBundle\Entity\Post',
           'Acme\FooBundle\Entity\Comment',
           'Acme\FooBundle\Entity\Tag',
        ]));
    }

    private function getMetadataFactory()
    {
        $driver = new XmlDriver(__DIR__.'/metadata', '.orm.xml');

        $connection = new Connection([], new Driver());

        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl($driver);
        $configuration->setProxyDir(sys_get_temp_dir());
        $configuration->setProxyNamespace('Foo\Proxy');

        $em = EntityManager::create($connection, $configuration);

        $factory = new DisconnectedClassMetadataFactory();
        $factory->setEntityManager($em);

        return $factory;
    }
}
