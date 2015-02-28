<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use JMS\Serializer\Serializer;
use Metadata\MetadataFactory;
use Metadata\PropertyMetadata;
use Lemon\RestBundle\Object\Definition;

class ClassNameToNgAdminConfigurationTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $objectDefinition;
    
    public function setUp()
    {
        $this->objectDefinition = new Definition('post', 'Acme\FooBundle\Entity\Post');
    }

    public function testTransformShouldAddClassFqdnAndEntityName()
    {
        $serializer = $this->getSerializerMock($this->getMetadataFactoryMock());
        $namingStrategy = $this->getNamingStrategyMock();
        $guesser = $this->getReferencedFieldGuesserMock();

        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $namingStrategy, $guesser);
        $transformedData = $transformer->transform($this->objectDefinition);

        $this->assertEquals('post', $transformedData['name']);
        $this->assertEquals('Acme\FooBundle\Entity\Post', $transformedData['class']);
    }

    public function testShouldTransformIntegerFieldIntoNumber()
    {
        $serializer = $this->getSerializerMock($this->getMetadataFactoryMock($this->getPropertyMetadataMock([
            'type' => [
                'name' => 'integer',
            ],
        ])));

        $namingStrategy = $this->getNamingStrategyMock('id');
        $guesser = $this->getReferencedFieldGuesserMock();

        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $namingStrategy, $guesser);

        $transformedData = $transformer->transform($this->className);
        $this->assertEquals([
            ['name' => 'id', 'type' => 'number'],
        ], $transformedData['fields']);
    }

    /** @dataProvider stringFieldsProvider */
    public function testShouldTransformStringFieldIntoStringOrTextDependingFieldName($fieldName, $expectedType)
    {
        $serializer = $this->getSerializerMock($this->getMetadataFactoryMock($this->getPropertyMetadataMock([
            'type' => [
                'name' => 'string',
            ],
            'reflection' => (object) [
                'name' => $fieldName,
            ]
        ])));

        $namingStrategy = $this->getNamingStrategyMock($fieldName);
        $guesser = $this->getReferencedFieldGuesserMock();
        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $namingStrategy, $guesser);

        $transformedData = $transformer->transform($this->className);
        $this->assertEquals([
            ['name' => $fieldName, 'type' => $expectedType],
        ], $transformedData['fields']);
    }

    public function stringFieldsProvider()
    {
        return [
            ['title', 'string'],
            ['name', 'string'],
            ['body', 'text'],
            ['content', 'text'],
            ['details', 'text'],
        ];
    }

    public function testShouldTransformArrayCollectionIntoReferencedListFieldWithRelationshipColumn()
    {
        $serializer = $this->getSerializerMock($this->getMetadataFactoryMock($this->getPropertyMetadataMock([
            'type' => [
                'name' => 'ArrayCollection',
                'params' => [
                    ['name' => 'Acme\FooBundle\Entity\Comment'],
                ]
            ],
            'reflection' => (object) [
                'name' => 'post',
            ]
        ])));
        $namingStrategy = $this->getNamingStrategyMock('comments');
        $guesser = $this->getReferencedFieldGuesserMock('title');

        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $namingStrategy, $guesser);

        $transformedData = $transformer->transform($this->className);
        $this->assertEquals([[
            'name' => 'comments',   
            'type' => 'referenced_list',
            'referencedEntity' => [
                'name' => 'comment',
                'class' => 'Acme\FooBundle\Entity\Comment',
            ],
            'referencedField' => 'title',
        ]], $transformedData['fields']);
    }

    private function getPropertyMetadataMock(array $properties = [])
    {
        $propertyMetadata = $this->getMockBuilder('JMS\Serializer\Metadata\PropertyMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($properties as $propertyName => $propertyValue) {
            $propertyMetadata->$propertyName = $propertyValue;
        }

        return $propertyMetadata;
    }

    private function getMetadataFactoryMock(PropertyMetadata $propertyMetadata = null)
    {
        if (!$propertyMetadata) {
            $propertyMetadata = $this->getPropertyMetadataMock();
        }

        $classMetadata = $this->getMockBuilder('Metadata\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $classMetadata->propertyMetadata = [$propertyMetadata];

        $metadataFactory = $this->getMockBuilder('Metadata\MetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataFactory->expects($this->any())
            ->method('getMetadataForClass')
            ->willReturn($classMetadata);

        return $metadataFactory;
    }

    private function getSerializerMock(MetadataFactory $factory)
    {
        $serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($factory);

        return $serializer;
    }

    private function getNamingStrategyMock($expectedName = null)
    {
        $strategy = $this->getMockBuilder('JMS\Serializer\Naming\PropertyNamingStrategyInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $strategy->expects($this->any())
            ->method('translateName')
            ->willReturn($expectedName);

        return $strategy;
    }

    private function getReferencedFieldGuesserMock($guessedField = null)
    {
        $guesser = $this->getMockBuilder('marmelab\NgAdminGeneratorBundle\Guesser\ReferencedFieldGuesser')
            ->disableOriginalConstructor()
            ->getMock();

        $guesser->expects($this->any())
            ->method('guess')
            ->willReturn($guessedField);

        return $guesser;
    }
}
