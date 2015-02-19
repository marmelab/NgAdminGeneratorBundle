<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use JMS\Serializer\Serializer;

class EntityToEntityWithoutExcludedFieldsTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformShouldKeepOnlySerializedFields()
    {
        $serializer = $this->getSerializerMock(['id', 'title', 'body']);
        $transformer = new EntityToEntityWithoutExcludedFieldsTransformer($serializer);

        $entity = [
            'class' => 'Acme\FooBundle\Post',
            'fields' => [
                'id' => ['name' => 'id'],
                'title' => ['name' => 'title'],
                'slug' => ['name' => 'slug'],
                'body' => ['name' => 'body'],
                'created_at' => ['name' => 'created_at'],
            ],
        ];

        $this->assertEquals(['id', 'title', 'body'], array_keys($transformer->transform($entity)['fields']));
    }

    private function getSerializerMock(array $exportedFieldNames = [])
    {
        $metadata = new \StdClass();
        $metadata->propertyMetadata = array_combine($exportedFieldNames, $exportedFieldNames);

        $metadataFactory = $this->getMockBuilder('Metadata\MetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataFactory->expects($this->once())
            ->method('getMetadataForClass')
            ->willReturn($metadata);

        $serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        return $serializer;
    }
}
