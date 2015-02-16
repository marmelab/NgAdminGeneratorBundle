<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Retriever;

use marmelab\NgAdminGeneratorBundle\Retriever\EntityConfigurationRetriever;

class RestEntitiesRetrieverTest extends \PHPUnit_Framework_TestCase
{
    public function testRetrieveEntityConfiguration()
    {
        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $metaData = new \StdClass();
        $metaData->fieldMappings = [
            'id'    => ['fieldName' => 'id', 'type' => 'integer'],
            'title' => ['fieldName' => 'title', 'type' => 'string'],
            'body'  => ['fieldName' => 'body', 'type' => 'text'],
        ];

        $entityManagerMock->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo('Acme\FooBundle\Entity\Post'))
            ->will($this->returnValue($metaData));

        $retriever = new EntityConfigurationRetriever($entityManagerMock);
        $this->assertEquals([
            ['name' => 'id', 'type' => 'integer'],
            ['name' => 'title', 'type' => 'string'],
            ['name' => 'body', 'type' => 'text'],
        ], $retriever->retrieveEntityConfiguration('Acme\FooBundle\Entity\Post'));
    }
}
