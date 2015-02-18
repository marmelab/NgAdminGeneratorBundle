<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Transformer;

use marmelab\NgAdminGeneratorBundle\Transformer\EntityReferencedFieldNameToMeaningfulNameTransformer;

class EntityReferencedFieldNameToMeaningfulNameTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldRenameReferencedFieldToBestChoiceForReferenceFields()
    {
        $entity = [
            'post_id' => [
                'type' => 'reference',
                'name' => 'post_id',
                'referencedEntity' => [
                    'name' => 'post',
                    'class' => 'Lemon\RestDemoBundle\Entity\Post',
                ],
                'referencedField' => 'id'
            ]
        ];

        $metadataFactory = $this->getMetadataFactoryMock('Lemon\RestDemoBundle\Entity\Post', ['id', 'title']);
        $transformer = new EntityReferencedFieldNameToMeaningfulNameTransformer($metadataFactory);
        $transformedEntity = $transformer->transform($entity);

        $this->assertEquals($transformedEntity['post_id']['referencedField'], 'title');
    }

    public function testShouldRenameReferencedFieldToBestChoiceForReferenceManyFields()
    {
        $entity = [
            'tags' => [
                'type' => 'reference_many',
                'name' => 'tags',
                'referencedEntity' => [
                    'name' => 'tag',
                    'class' => 'Lemon\RestDemoBundle\Entity\Tag',
                ],
                'referencedField' => 'id'
            ]
        ];

        $metadataFactory = $this->getMetadataFactoryMock('Lemon\RestDemoBundle\Entity\Tag', ['id', 'name']);
        $transformer = new EntityReferencedFieldNameToMeaningfulNameTransformer($metadataFactory);
        $transformedEntity = $transformer->transform($entity);

        $this->assertEquals($transformedEntity['tags']['referencedField'], 'name');
    }

    private function getMetadataFactoryMock($entityClass, array $fieldNames = [])
    {
        $classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $classMetadata->expects($this->once())
            ->method('getFieldNames')
            ->willReturn($fieldNames);

        $factoryMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock->expects($this->once())
            ->method('getMetadataFor')
            ->with($entityClass)
            ->willReturn($classMetadata);

        return $factoryMock;
    }
}
